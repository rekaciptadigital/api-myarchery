<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventEliminationGroupMatch;
use App\Models\ArcheryEventEliminationGroupTeams;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventEliminationMember;
use App\Models\ArcheryScoring;
use App\Models\ArcheryScoringEliminationGroup;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Abstracts\Retrieval;
use Exception;
use Illuminate\Support\Facades\DB;

class ResetScoringEliminasi extends Retrieval
{
    public function getDescription()
    {
        return "Reset Scoring Eliminasi";
    }

    protected function process($parameters)
    {
        // dapatkan semua param
        $elimination_id = $parameters->get("elimination_id");
        $category_id = $parameters->get("category_id");
        $round = $parameters->get("round");
        $match = $parameters->get("match");

        $category =  ArcheryEventCategoryDetail::select("archery_event_category_details.*", "archery_master_team_categories.type")
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
            ->where("archery_event_category_details.id", $category_id)
            ->first();

        if (!$category) {
            throw new BLoCException("category not found");
        }

        if (strtolower($category->type) == "individual") {
            return $this->resetScoringIndividu($elimination_id, $round, $match);
        } else {
            return $this->resetScoringGroup($elimination_id, $round, $match);
        }

        return -1;
    }

    private function resetScoringIndividu($elimination_id, $round, $match)
    {
        // tangkap match berdasarkan round, match dan elimination
        $elimination_match = ArcheryEventEliminationMatch::select("archery_event_elimination_matches.*")
            ->join("archery_event_eliminations", "archery_event_eliminations.id", "=", "archery_event_elimination_matches.event_elimination_id")
            ->where("archery_event_elimination_matches.event_elimination_id", $elimination_id)
            ->where("archery_event_elimination_matches.round", $round)
            ->where("archery_event_elimination_matches.match", $match)
            ->get();

        // return $elimination_match;

        if ($elimination_match->count() < 1 || $elimination_match->count() > 2) {
            throw new BLoCException("match tidak valid");
        }

        try {
            DB::beginTransaction();
            $elimination = ArcheryEventElimination::find($elimination_id);
            if (!$elimination) {
                throw new BLoCException("elimination not found");
            }

            $current_round = $this->checkRound($elimination->count_participant, $round);
            if ($current_round == "other") {
                foreach ($elimination_match as $key => $em) {
                    $elimination_member = ArcheryEventEliminationMember::find($em->elimination_member_id);
                    if ($elimination_member) {
                        $elimination_member->elimination_ranked = 0;
                        $elimination_member->save();
                    }

                    if ($em->win == 1) {
                        $next_match = ArcheryEventEliminationMatch::where("event_elimination_id", $elimination_id)
                            ->where("round", ">", $round)
                            ->where("elimination_member_id", $em->elimination_member_id)
                            ->get();

                        if ($next_match->count() > 1) {
                            throw new Exception("harap lakukan pembatalan dari round terakhir", 400);
                        }

                        $match_after = ArcheryEventEliminationMatch::where("event_elimination_id", $elimination_id)
                            ->where("round", $round + 1)
                            ->where("elimination_member_id", $em->elimination_member_id)
                            ->first();

                        if (!$match_after) {
                            throw new Exception("match after not found", 404);
                        }

                        $full_match_after = ArcheryEventEliminationMatch::where("event_elimination_id", $elimination_id)
                            ->where("round", $match_after->round)
                            ->where("match", $match_after->match)
                            ->get();

                        foreach ($full_match_after as $key => $fma) {
                            $fma->win = 0;
                            $fma->save();

                            $elimination_member_next = ArcheryEventEliminationMember::find($fma->elimination_member_id);
                            if ($elimination_member_next) {
                                $elimination_member_next->elimination_ranked = 0;
                                $elimination_member_next->save();
                            }

                            if ($fma->elimination_member_id == $em->elimination_member_id) {
                                $fma->elimination_member_id = 0;
                                $fma->save();
                            }

                            $scoring = ArcheryScoring::where("type", 2)->where("item_id", $fma->id)->where("item_value", "archery_event_elimination_matches")->first();
                            if ($scoring) {
                                $scoring->delete();
                            }
                        }
                    }

                    $em->win = 0;
                    $em->save();
                }
            }

            if ($current_round == "semi_final") {
                foreach ($elimination_match as $key => $em) {

                    $elimination_member = ArcheryEventEliminationMember::find($em->elimination_member_id);
                    if ($elimination_member) {
                        $elimination_member->elimination_ranked = 0;
                        $elimination_member->save();
                    }

                    $next_match = ArcheryEventEliminationMatch::where("event_elimination_id", $elimination_id)
                        ->where("round", ">", $round)
                        ->where("elimination_member_id", $em->elimination_member_id)
                        ->get();

                    if ($next_match->count() > 1) {
                        throw new Exception("harap lakukan pembatalan dari round terakhir", 400);
                    }

                    if ($em->win == 1) {
                        $match_after = ArcheryEventEliminationMatch::where("event_elimination_id", $elimination_id)
                            ->where("round", $round + 1)
                            ->where("elimination_member_id", $em->elimination_member_id)
                            ->first();

                        if (!$match_after) {
                            throw new Exception("match after not found", 404);
                        }

                        $full_match_after = ArcheryEventEliminationMatch::where("event_elimination_id", $elimination_id)
                            ->where("round", $match_after->round)
                            ->where("match", $match_after->match)
                            ->get();

                        foreach ($full_match_after as $key => $fma) {
                            $fma->win = 0;
                            $fma->save();

                            $elimination_member_next = ArcheryEventEliminationMember::find($fma->elimination_member_id);
                            if ($elimination_member_next) {
                                $elimination_member_next->elimination_ranked = 0;
                                $elimination_member_next->save();
                            }

                            if ($fma->elimination_member_id == $em->elimination_member_id) {
                                $fma->elimination_member_id = 0;
                                $fma->save();
                            }

                            $scoring = ArcheryScoring::where("type", 2)->where("item_id", $fma->id)->where("item_value", "archery_event_elimination_matches")->first();
                            if ($scoring) {
                                $scoring->delete();
                            }
                        }
                    } else {
                        $round_juara_3 = ArcheryEventEliminationMatch::where("event_elimination_id", $elimination_id)->where("round", $round + 2)
                            ->where("elimination_member_id", $em->elimination_member_id)
                            ->first();

                        if (!$round_juara_3) {
                            throw new Exception("perebutan juara 3 tidak ada", 404);
                        }

                        $full_match_round_juara_3 = ArcheryEventEliminationMatch::where("event_elimination_id", $elimination_id)
                            ->where("round", $round_juara_3->round)
                            ->where("match", $round_juara_3->match)
                            ->get();

                        foreach ($full_match_round_juara_3 as $key => $fmrj) {
                            $fmrj->win = 0;
                            $fmrj->save();

                            $elimination_member_round_juara_3 = ArcheryEventEliminationMember::find($fmrj->elimination_member_id);
                            if ($elimination_member_round_juara_3) {
                                $elimination_member_round_juara_3->elimination_ranked = 0;
                                $elimination_member_round_juara_3->save();
                            }

                            if ($fmrj->elimination_member_id == $em->elimination_member_id) {
                                $fmrj->elimination_member_id = 0;
                                $fmrj->save();
                            }

                            $scoring = ArcheryScoring::where("type", 2)->where("item_id", $fmrj->id)->where("item_value", "archery_event_elimination_matches")->first();
                            if ($scoring) {
                                $scoring->delete();
                            }
                        }
                    }

                    $em->win = 0;
                    $em->save();
                }
            }

            if ($current_round == "final" || $current_round == "juara3") {
                foreach ($elimination_match as $key => $em) {
                    $em->win = 0;
                    $em->save();

                    $elimination_member_final = ArcheryEventEliminationMember::find($em->elimination_member_id);
                    if ($elimination_member_final) {
                        $elimination_member_final->elimination_ranked = 0;
                        $elimination_member_final->save();
                    }
                }
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new BLoCException($th->getMessage());
        }

        return "success";
    }

    private function resetScoringGroup($elimination_id, $round, $match)
    {
        // tangkap match berdasarkan round, match dan elimination
        $elimination_match_group = ArcheryEventEliminationGroupMatch::select("archery_event_elimination_group_match.*")
            ->join("archery_event_elimination_group", "archery_event_elimination_group.id", "=", "archery_event_elimination_group_match.elimination_group_id")
            ->where("archery_event_elimination_group_match.elimination_group_id", $elimination_id)
            ->where("archery_event_elimination_group_match.round", $round)
            ->where("archery_event_elimination_group_match.match", $match)
            ->get();

        // return $elimination_match_group;

        if ($elimination_match_group->count() < 1 || $elimination_match_group->count() > 2) {
            throw new BLoCException("match tidak valid");
        }

        try {
            DB::beginTransaction();
            $elimination_group = ArcheryEventEliminationGroup::find($elimination_id);
            if (!$elimination_group) {
                throw new BLoCException("elimination_group not found");
            }

            $current_round = $this->checkRound($elimination_group->count_participant, $round);
            if ($current_round == "other") {
                foreach ($elimination_match_group as $key => $emg) {
                    $group_team = ArcheryEventEliminationGroupTeams::find($emg->group_team_id);
                    if ($group_team) {
                        $group_team->elimination_ranked = 0;
                        $group_team->save();
                    }

                    if ($emg->win == 1) {
                        $next_match = ArcheryEventEliminationGroupMatch::where("elimination_group_id", $elimination_id)
                            ->where("round", ">", $round)
                            ->where("group_team_id", $emg->group_team_id)
                            ->get();

                        if ($next_match->count() > 1) {
                            throw new Exception("harap lakukan pembatalan dari round terakhir", 400);
                        }

                        $match_after = ArcheryEventEliminationGroupMatch::where("elimination_group_id", $elimination_id)
                            ->where("round", $round + 1)
                            ->where("group_team_id", $emg->group_team_id)
                            ->first();

                        if (!$match_after) {
                            throw new Exception("match after not found", 404);
                        }

                        $full_match_after = ArcheryEventEliminationGroupMatch::where("elimination_group_id", $elimination_id)
                            ->where("round", $match_after->round)
                            ->where("match", $match_after->match)
                            ->get();

                        foreach ($full_match_after as $key => $fma) {
                            $fma->win = 0;
                            $fma->save();

                            $elimination_group_next = ArcheryEventEliminationGroupTeams::find($fma->group_team_id);
                            if ($elimination_group_next) {
                                $elimination_group_next->elimination_ranked = 0;
                                $elimination_group_next->save();
                            }

                            if ($fma->group_team_id == $emg->group_team_id) {
                                $fma->group_team_id = 0;
                                $fma->save();
                            }

                            $scoring = ArcheryScoringEliminationGroup::where("elimination_match_group_id", $fma->id)->first();
                            if ($scoring) {
                                $scoring->delete();
                            }
                        }
                    }

                    $emg->win = 0;
                    $emg->save();
                }
            }

            if ($current_round == "semi_final") {
                foreach ($elimination_match_group as $key => $emg) {

                    $group_team = ArcheryEventEliminationGroupTeams::find($emg->group_team_id);
                    if ($group_team) {
                        $group_team->elimination_ranked = 0;
                        $group_team->save();
                    }

                    $next_match = ArcheryEventEliminationGroupMatch::where("elimination_group_id", $elimination_id)
                        ->where("round", ">", $round)
                        ->where("group_team_id", $emg->group_team_id)
                        ->get();

                    if ($next_match->count() > 1) {
                        throw new Exception("harap lakukan pembatalan dari round terakhir", 400);
                    }

                    if ($emg->win == 1) {
                        $match_after = ArcheryEventEliminationGroupMatch::where("elimination_group_id", $elimination_id)
                            ->where("round", $round + 1)
                            ->where("group_team_id", $emg->group_team_id)
                            ->first();

                        if (!$match_after) {
                            throw new Exception("match after not found", 404);
                        }

                        $full_match_after = ArcheryEventEliminationGroupMatch::where("elimination_group_id", $elimination_id)
                            ->where("round", $match_after->round)
                            ->where("match", $match_after->match)
                            ->get();

                        foreach ($full_match_after as $key => $fma) {
                            $fma->win = 0;
                            $fma->save();

                            $elimination_group_next = ArcheryEventEliminationGroupTeams::find($fma->group_team_id);
                            if ($elimination_group_next) {
                                $elimination_group_next->elimination_ranked = 0;
                                $elimination_group_next->save();
                            }

                            if ($fma->group_team_id == $emg->group_team_id) {
                                $fma->group_team_id = 0;
                                $fma->save();
                            }

                            $scoring = ArcheryScoringEliminationGroup::where("elimination_match_group_id", $fma->id)->first();
                            if ($scoring) {
                                $scoring->delete();
                            }
                        }
                    } else {
                        $round_juara_3 = ArcheryEventEliminationGroupMatch::where("elimination_group_id", $elimination_id)->where("round", $round + 2)
                            ->where("group_team_id", $emg->group_team_id)
                            ->first();

                        if (!$round_juara_3) {
                            throw new Exception("perebutan juara 3 tidak ada", 404);
                        }

                        $full_match_round_juara_3 = ArcheryEventEliminationGroupMatch::where("elimination_group_id", $elimination_id)
                            ->where("round", $round_juara_3->round)
                            ->where("match", $round_juara_3->match)
                            ->get();

                        foreach ($full_match_round_juara_3 as $key => $fmrj) {
                            $fmrj->win = 0;
                            $fmrj->save();

                            $elimination_member_round_juara_3 = ArcheryEventEliminationGroupTeams::find($fmrj->group_team_id);
                            if ($elimination_member_round_juara_3) {
                                $elimination_member_round_juara_3->elimination_ranked = 0;
                                $elimination_member_round_juara_3->save();
                            }


                            if ($fmrj->group_team_id == $emg->group_team_id) {
                                $fmrj->group_team_id = 0;
                                $fmrj->save();
                            }

                            $scoring = ArcheryScoringEliminationGroup::where("elimination_match_group_id", $fmrj->id)->first();
                            if ($scoring) {
                                $scoring->delete();
                            }
                        }
                    }

                    $emg->win = 0;
                    $emg->save();
                }
            }

            if ($current_round == "final" || $current_round == "juara3") {
                foreach ($elimination_match_group as $key => $emg) {
                    $emg->win = 0;
                    $emg->save();

                    $elimination_member_final = ArcheryEventEliminationGroupTeams::find($emg->group_team_id);
                    if ($elimination_member_final) {
                        $elimination_member_final->elimination_ranked = 0;
                        $elimination_member_final->save();
                    }
                }
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new BLoCException($th->getMessage());
        }

        return "success";
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

    private function checkRound($elimination_count, $round)
    {
        if ($elimination_count == 32) {
            if ($round == 4) {
                return "semi_final";
            } elseif ($round == 5) {
                return "final";
            } elseif ($round == 6) {
                return "juara3";
            } else {
                return "other";
            }
        }

        if ($elimination_count == 16) {
            if ($round == 3) {
                return "semi_final";
            } elseif ($round == 4) {
                return "final";
            } elseif ($round == 5) {
                return "juara3";
            } else {
                return "other";
            }
        }

        if ($elimination_count == 8) {
            if ($round == 2) {
                return "semi_final";
            } elseif ($round == 3) {
                return "final";
            } elseif ($round == 4) {
                return "juara3";
            } else {
                return "other";
            }
        }

        if ($elimination_count == 4) {
            if ($round == 1) {
                return "semi_final";
            } elseif ($round == 2) {
                return "final";
            } elseif ($round == 3) {
                return "juara3";
            } else {
                return "other";
            }
        }

        throw new BLoCException("not ready");
    }
}

// tangkap round saat ini


// apabila dia round biasa
// ambil match nya dan jadikan win 0 di setiap elimination member
// dan elimination_ranked = 0  untuk kedua member
// ambil match round setelah nya jadikan win = 0 untuk kedua slot
// dan elimination_ranked = 0  untuk kedua member
// jika member == dengan index maka ubah elimination member = 0
// ambil archery scoring kedua match jika ada lalu hapus




// apabila dia round semi final
// ambil match nya dan jadikan win 0 di setiap elimination member
// dan elimination_ranked = 0  untuk kedua member
// ambil match round final jadikan win = 0 untuk kedua slot
// dan elimination_ranked = 0  untuk kedua member
// jika member == dengan index maka ubah elimination member = 0
// ambil archery scoring kedua match jika ada lalu hapus
// ambil elimination member yang kalah pada round yang dibatalkan
// ambil round untuk memperebutkan peringkat 3
// ubah win kedua match = 0
// dan elimination_ranked = 0  untuk kedua member
// dapatkan scoring kedua match jika ada hapus
// jika index == member ubah jadi 0

// apabila di round final
// ubah win kedua match jadi 0
// dan elimination_ranked = 0  untuk kedua member

// apabila di round perebutan juara 3
// ambil match nya dan jadikan win 0 di setiap elimination member
// dan elimination_ranked = 0  untuk kedua member
