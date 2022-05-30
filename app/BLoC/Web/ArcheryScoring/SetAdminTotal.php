<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryScoring;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationMatch;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Abstracts\Retrieval;

class SetAdminTotal extends Retrieval
{
    public function getDescription()
    {
        return "memberi nilan admin_total dari halaman get list skoring eliminasi";
    }

    protected function process($parameters)
    {
        // 1. tangkap param match, round, elimination_id, dan type: 
        $elimination_id = $parameters->get("elimination_id");
        $match = $parameters->get("match");
        $round = $parameters->get("round");
        $member_id = $parameters->get("member_id");
        $admin_total = $parameters->get("admin_total");
        // 2. pastikan elimination id tsb terdapat di db
        $elimination = ArcheryEventElimination::find($elimination_id);
        if (!$elimination) {
            throw new BLoCException("data eliminasi tidak ditemukan");
        }
        // 3. cari di tabel elimination match yang elimination, match, member_id dan round sesuai
        $elimination_match = ArcheryEventEliminationMatch::select(
            "archery_event_elimination_members.member_id",
            "archery_event_elimination_matches.*"
        )
            ->join("archery_event_elimination_members", "archery_event_elimination_matches.elimination_member_id", "=", "archery_event_elimination_members.id")
            ->where("archery_event_elimination_matches.event_elimination_id", $elimination_id)
            ->where("archery_event_elimination_matches.round", $round)
            ->where("archery_event_elimination_matches.match", $match)
            ->where("archery_event_elimination_members.member_id", $member_id)
            ->first();

        if (!$elimination_match) {
            throw new BLoCException("elimination match tidak valid");
        }
        // 4. cari di tabel skoring dengan id member dari table match dan type 2 dan pastikan ketersediaannya dan item_id yang sesuai dengan elimination match
        $scooring = ArcheryScoring::where("type", 2)
            ->where("participant_member_id", $elimination_match->member_id)
            ->where("item_id", $elimination_match->id)
            ->where("item_value", "archery_event_elimination_matches")
            ->first();

        // 5. jika belum tersedia insertkan hal yang mandatory pada tabel archery scoring beserta admin total ke table scooring
        if (!$scooring) {
            if ($elimination->elimination_scoring_type == 0) {
                throw new BLoCException("elimination scooring type belum ditentukan");
            }

            if ($elimination->elimination_scoring_type == 1) {
                $scoring_detail = ArcheryScoring::makeEliminationScoringTypePointFormat();
            }

            if ($elimination->elimination_scoring_type == 2) {
                $scoring_detail = ArcheryScoring::makeEliminationScoringTypeTotalFormat();
            }

            $scooring = new ArcheryScoring();
            $scooring->participant_member_id = $member_id;
            $scooring->total = 0;
            $scooring->scoring_session = 1;
            $scooring->scoring_detail = json_encode($scoring_detail);
            $scooring->type = 2;
            $scooring->scoring_log = json_encode($elimination_match);
            $scooring->item_value = "archery_event_elimination_matches";
            $scooring->item_id = $elimination_match->id;
        }
        // jika tersedia update hanya field admin total
        $scooring->admin_total = $admin_total;
        $scooring->save();

        return $scooring;
    }

    protected function validation($parameters)
    {
        return [
            "elimination_id" => "required",
            "round" => "required",
            "match" => "required",
            "member_id" => "required",
            "admin_total" => "required"
        ];
    }
}
