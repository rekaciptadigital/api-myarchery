<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Libraries\EliminationFormat;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventEliminationGroupMatch;
use App\Models\ArcheryEventEliminationGroupTeams;
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
        $category_id = $parameters->get("category_id");

        $category = ArcheryEventCategoryDetail::select("archery_event_category_details.*", "archery_master_team_categories.type")
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
            ->where("archery_event_category_details.id", $category_id)
            ->first();

        if (!$category) {
            throw new BLoCException("category not found");
        }

        if (strtolower($category->type) == "team") {
            return $this->setSavePermanentEliminationTeam($elimination_id, $round, $match);
        } else {
            return $this->setSavePermanentEliminationIndividu($elimination_id, $round, $match);
        }

        throw new BLoCException("failed save permanent");
    }

    private function setSavePermanentEliminationIndividu($elimination_id, $round, $match)
    {
        // pastikan terdapat event elimination berdasarkan param elimination id
        $elimination = ArcheryEventElimination::find($elimination_id);
        if (!$elimination) {
            throw new BLoCException("elimination tidak ditemukan");
        }

        // cari di tabel match yang elimination_id, round, match sesuai dengan yang ada di parameter
        $get_member_match = ArcheryEventEliminationMatch::select(
            "archery_event_elimination_members.member_id",
            "archery_event_elimination_matches.*",
            "archery_scorings.admin_total",
            "archery_scorings.scoring_detail"
        )
            ->join("archery_event_elimination_members", "archery_event_elimination_matches.elimination_member_id", "=", "archery_event_elimination_members.id")
            ->leftJoin("archery_scorings", "archery_scorings.item_id", "=", "archery_event_elimination_matches.id")
            ->where("archery_event_elimination_matches.event_elimination_id", $elimination_id)
            ->where("round", $round)
            ->where("match", $match)
            ->orderBy("round")
            ->orderBy("match")
            ->orderBy("index")
            ->get();

        // cek valid atau tidaknya match tersebut
        if ($get_member_match->count() != 2) {
            throw new BLoCException("match tidak valid");
        }

        // lakukan perulangan

        $scoring_detail_1 = json_decode($get_member_match[0]->scoring_detail);
        $scoring_detail_2 = json_decode($get_member_match[1]->scoring_detail);

        foreach ($get_member_match as $key => $value) {
            if ($value->admin_total === null) {
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
            $result_1 = $scoring_detail_1->result;
            $result_2 = $scoring_detail_2->result;
            if ($result_1 > $result_2) {
                $win_member = $get_member_match[0]->id;
            } elseif ($result_2 > $result_1) {
                $win_member = $get_member_match[1]->id;
            } else {
                $result_shot_of_1 = 0;
                foreach ($scoring_detail_1->extra_shot as $key => $value) {
                    if ($value->score == "") {
                        continue;
                    }
                    $result_shot_of_1 = $result_shot_of_1 + $value->score;
                }

                $result_shot_of_2 = 0;
                foreach ($scoring_detail_2->extra_shot as $key => $value) {
                    if ($value->score == "") {
                        continue;
                    }
                    $result_shot_of_2 = $result_shot_of_2 + $value->score;
                }

                if ($result_shot_of_1 > $result_shot_of_2) {
                    $win_member = $get_member_match[0]->id;
                } elseif ($result_shot_of_2 > $result_shot_of_1) {
                    $win_member = $get_member_match[1]->id;
                } else {
                    throw new BLoCException("hasil seri");
                }
            }
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

            $value->result = json_decode($value->scoring_detail)->result;
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

    private function setSavePermanentEliminationTeam($elimination_id, $round, $match)
    {
        // pastikan terdapat event elimination berdasarkan param elimination id
        $elimination = ArcheryEventEliminationGroup::find($elimination_id);
        if (!$elimination) {
            throw new BLoCException("elimination group tidak ditemukan");
        }

        // cari di tabel match yang elimination_id, round, match sesuai dengan yang ada di parameter
        $get_member_match = ArcheryEventEliminationGroupMatch::select(
            "archery_event_elimination_group_teams.participant_id",
            "archery_event_elimination_group_match.*",
            "archery_scoring_elimination_group.admin_total",
            "archery_scoring_elimination_group.scoring_detail"
        )
            ->join("archery_event_elimination_group_teams", "archery_event_elimination_group_match.group_team_id", "=", "archery_event_elimination_group_teams.id")
            ->leftJoin("archery_scoring_elimination_group", "archery_scoring_elimination_group.elimination_match_group_id", "=", "archery_event_elimination_group_match.id")
            ->where("archery_event_elimination_group_match.elimination_group_id", $elimination_id)
            ->where("round", $round)
            ->where("match", $match)
            ->orderBy("round")
            ->orderBy("match")
            ->orderBy("index")
            ->get();

        // return $get_member_match;

        // cek valid atau tidaknya match tersebut
        if ($get_member_match->count() != 2) {
            throw new BLoCException("match tidak valid");
        }
        // lakukan perulangan
        foreach ($get_member_match as $key => $value) {
            if ($value->admin_total === null) {
                throw new BLoCException("skoring belum diinputkan");
            }
            // didalam perulangan pastikan belum ada yang win = 1
            if ($value->win == 1) {
                throw new BLoCException("match have winner");
            }
        }

        $scoring_detail_1 = json_decode($get_member_match[0]->scoring_detail);
        $scoring_detail_2 = json_decode($get_member_match[1]->scoring_detail);

        // bandingak admin_total keduanya untuk mendapatkan pemenang
        if ($get_member_match[0]->admin_total > $get_member_match[1]->admin_total) {
            $win_member = $get_member_match[0]->id;
        }

        if ($get_member_match[1]->admin_total > $get_member_match[0]->admin_total) {
            $win_member = $get_member_match[1]->id;
        }

        if ($get_member_match[1]->admin_total == $get_member_match[0]->admin_total) {
            $result_1 = $scoring_detail_1->result;
            $result_2 = $scoring_detail_2->result;
            if ($result_1 > $result_2) {
                $win_member = $get_member_match[0]->id;
            } elseif ($result_2 > $result_1) {
                $win_member = $get_member_match[1]->id;
            } else {
                $result_shot_of_1 = 0;
                foreach ($scoring_detail_1->extra_shot as $key => $value) {
                    if ($value->score == "") {
                        continue;
                    }
                    $result_shot_of_1 = $result_shot_of_1 + $value->score;
                }

                $result_shot_of_2 = 0;
                foreach ($scoring_detail_2->extra_shot as $key => $value) {
                    if ($value->score == "") {
                        continue;
                    }
                    $result_shot_of_2 = $result_shot_of_2 + $value->score;
                }

                if ($result_shot_of_1 > $result_shot_of_2) {
                    $win_member = $get_member_match[0]->id;
                } elseif ($result_shot_of_2 > $result_shot_of_1) {
                    $win_member = $get_member_match[1]->id;
                } else {
                    throw new BLoCException("hasil seri");
                }
            }
        }

        // lakukan perulangan kembali untuk set status pemenang tiap match
        foreach ($get_member_match as $key => $value) {
            $win = 0;
            if ($win_member == $value->id) {
                $win = 1;
            }
            $champion = EliminationFormat::EliminationChampion($elimination->count_participant, $round, $match, $win);
            if ($champion != 0) {
                ArcheryEventEliminationGroupTeams::where("id", $value->group_team_id)->update(["elimination_ranked" => $champion]);
            }
            if ($win == 1) {
                $value->win = $win;
            }

            $value->result = json_decode($value->scoring_detail)->result;
            $next = EliminationFormat::NextMatch($elimination->count_participant, $round, $match, $win);
            if (count($next) > 0) {
                ArcheryEventEliminationGroupMatch::where("round", $next["round"])
                    ->where("match", $next["match"])
                    ->where("index", $next["index"])
                    ->where("elimination_group_id", $elimination_id)
                    ->update(["group_team_id" => $value->group_team_id]);
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
            "category_id" => "required"
        ];
    }
}
