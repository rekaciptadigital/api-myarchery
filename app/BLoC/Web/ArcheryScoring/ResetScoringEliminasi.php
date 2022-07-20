<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationGroupMatch;
use App\Models\ArcheryEventEliminationGroupTeams;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventEliminationMember;
use App\Models\ArcheryScoring;
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
            foreach ($elimination_match as $value) {
                if ($value->win == 1) {
                    // reset pemenang dari match
                    $value->win = 0;
                    $value->save();

                    // reset posisi pada round setelah round saat ini
                    $next_match =  ArcheryEventEliminationMatch::where("event_elimination_id", $elimination_id)
                        ->where("round", ">", $round)
                        ->where("elimination_member_id", $value->elimination_member_id)
                        ->get();

                    if ($next_match->count() > 1) {
                        // throw new BLoCException("harap reset 1 per satu setiap round");
                        throw new Exception("harap reset 1 per satu setiap round", 401);
                    }

                    $match_after = ArcheryEventEliminationMatch::where("round", $next_match[0]->round)
                        ->where("match", $next_match[0]->match)
                        ->where("event_elimination_id", $elimination_id)
                        ->get();

                    foreach ($match_after as $ma) {
                        $scoring_elimination_next_match = ArcheryScoring::where("type", 2)
                            ->where("item_id", $ma->id)
                            ->where("item_value", "archery_event_elimination_matches")
                            ->first();
                        if ($scoring_elimination_next_match) {
                            $scoring_elimination_next_match->delete();
                        }

                        if ($ma->win == 1) {
                            $ma->win = 0;
                            $ma->save();
                        }
                    }

                    foreach ($next_match as $nm) {
                        $nm->elimination_member_id = 0;
                        $nm->save();
                    }
                } else {
                    // cek apakah pembatalan dilakukan di semifinal atau tidak
                    $is_semifinal = 0;
                    $elimination = ArcheryEventElimination::find($elimination_id);
                    if (!$elimination) {
                        throw new Exception("elimination not found", 404);
                    }

                    if ($elimination->count_participant == 32 && $value->round == 4) {
                        $is_semifinal = 1;
                    } elseif ($elimination->count_participant == 16 && $value->round == 3) {
                        $is_semifinal = 1;
                    } elseif ($elimination->count_participant == 8 && $value->round == 2) {
                        $is_semifinal = 1;
                    } elseif ($elimination->count_participant == 4 && $value->round == 1) {
                        $is_semifinal = 1;
                    }


                    $elimination_member = ArcheryEventEliminationMember::find($value->elimination_member_id);
                    if (!$elimination_member) {
                        throw new BLoCException("elimination member not found");
                    }

                    if ($is_semifinal == 1) {
                        // ambil round di perebutan juara 3
                        $match = ArcheryEventEliminationMatch::where("event_elimination_id", $elimination_id)
                            ->where("round", $value->round + 2)
                            ->where("elimination_member_id", $elimination_member->id)
                            ->first();

                        if (!$match) {
                            throw new Exception("match 3rd winer not found", 404);
                        }

                        $match->elimination_member_id = 0;
                        $match->win = 0;
                        $match->save();
                    }

                    // reset peringkat eliminasi di tabel elimination member
                    $elimination_member->elimination_ranked = 0;
                    $elimination_member->save();
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
        $elimination_group_match = ArcheryEventEliminationGroupMatch::select("archery_event_elimination_group_match.*")
            ->join("archery_event_elimination_group", "archery_event_elimination_group.id", "=", "archery_event_elimination_group_match.elimination_group_id")
            ->where("archery_event_elimination_group_match.elimination_group_id", $elimination_id)
            ->where("archery_event_elimination_group_match.round", $round)
            ->where("archery_event_elimination_group_match.match", $match)
            ->get();


        if ($elimination_group_match->count() < 1 || $elimination_group_match->count() > 2) {
            throw new BLoCException("match tidak valid");
        }

        foreach ($elimination_group_match as $key => $value) {
            if ($value->win == 1) {
                // reset pemenang dari match
                $value->win = 0;
                $value->save();

                // reset posisi pada round setelah round saat ini
                $next_match =  ArcheryEventEliminationGroupMatch::where("elimination_group_id", $elimination_id)
                    ->where("round", ">", $round)
                    ->where("group_team_id", $value->group_team_id)
                    ->get();

                if ($next_match->count() > 1) {
                    throw new BLoCException("harap reset 1 per satu setiap round");
                }

                foreach ($next_match as $nm) {
                    $nm->group_team_id = 0;
                    $nm->save();
                }
            } else {
                // reset peringkat eliminasi di tabel elimination member
                $elimination_group_team = ArcheryEventEliminationGroupTeams::find($value->group_team_id);
                if (!$elimination_group_team) {
                    throw new BLoCException("elimination group team not found");
                }

                $elimination_group_team->elimination_ranked = 0;
                $elimination_group_team->save();
            }
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
}
