<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Libraries\EliminationFormat;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventEliminationMember;
use App\Models\ArcherySeriesUserPoint;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Abstracts\Retrieval;

class SetSavePermanentElimination extends Retrieval
{
    public function getDescription()
    {
        return "memberi nilan admin_total dari halaman get list skoring eliminasi";
    }

    protected function process($parameters)
    {
        // tangkap param -> elimination_id, round, match
        $elimination_id = $parameters->get("elimination_id");
        $match = $parameters->get("match");
        $round = $parameters->get("round");
        // pastikan terdapat event elimination berdasarkan param elimination id
        $elimination = ArcheryEventElimination::find($elimination_id);
        if (!$elimination) {
            throw new BLoCException("elimination tidak ditemukan");
        }

        // cari di tabel match yang elimination_id, round, match sesuai dengan yang ada di parameter
        $get_member_match = ArcheryEventEliminationMatch::select(
            "archery_event_elimination_members.member_id",
            "archery_event_elimination_matches.*",
            "archery_scorings.admin_total"
        )
            ->join("archery_event_elimination_members", "archery_event_elimination_matches.elimination_member_id", "=", "archery_event_elimination_members.id")
            ->leftJoin("archery_scorings", "archery_scorings.item_id", "=", "archery_event_elimination_matches.id")
            ->where("archery_event_elimination_matches.event_elimination_id", $elimination_id)
            ->where("round", $round)
            ->where("match", $match)
            ->orderBy("round")
            ->orderBy("match")
            ->get();

        // cek valid atau tidaknya match tersebut
        if ($get_member_match->count() != 2) {
            throw new BLoCException("match tidak valid");
        }
        // lakukan perulangan
        foreach ($get_member_match as $key => $value) {
            if ($value->admin_total == null) {
                throw new BLoCException("skoring belum diinputkan");
            }
            // didalam perulangan pastikan belum ada yang win = 1
            if ($value->win == 1) {
                throw new BLoCException("match have winner");
            }
        }

        // bandingak admin_total keduanya untuk mendapatkan pemenang
        if ($get_member_match[0]->admin_total > $get_member_match[1]->admin_total) {
            $win_member = $get_member_match[0]->id;
        }

        if ($get_member_match[1]->admin_total > $get_member_match[0]->admin_total) {
            $win_member = $get_member_match[1]->id;
        }

        if ($get_member_match[1]->admin_total == $get_member_match[0]->admin_total) {
            throw new BLoCException("hasil seri tidak dapat menentukan pemenang");
        }

        // lakukan perulangan kembali untuk set status pemenang tiap match
        foreach ($get_member_match as $key => $value) {
            $win = 0;
            if ($win_member == $value->id) {
                $win = 1;
            }
            $champion = EliminationFormat::EliminationChampion($elimination->count_participant, $round, $match, $win);
            if ($champion != 0) {
                ArcherySeriesUserPoint::setPoint($value->member_id, "elimination", $champion);
                ArcheryEventEliminationMember::where("id", $value->elimination_member_id)->update(["elimination_ranked" => $champion]);
            }
            if ($win == 1) {
                $value->win = $win;
            }

            $value->result = $value->admin_total;
            $next = EliminationFormat::NextMatch($elimination->count_participant, $round, $match, $win);
            if (count($next) > 0) {
                ArcheryEventEliminationMatch::where("round", $next["round"])
                    ->where("match", $next["match"])
                    ->where("index", $next["index"])
                    ->where("event_elimination_id", $elimination_id)
                    ->update(["elimination_member_id" => $value->elimination_member_id]);
            }
            $value->save();
        }

        return $get_member_match;
    }

    protected function validation($parameters)
    {
        return [
            "elimination_id" => "required",
            "round" => "required",
            "match" => "required",
        ];
    }
}
