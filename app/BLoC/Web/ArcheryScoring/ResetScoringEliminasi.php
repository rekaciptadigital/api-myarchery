<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventEliminationGroupMatch;
use App\Models\ArcheryEventEliminationGroupTeams;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventEliminationMember;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Abstracts\Retrieval;

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

        $category =  ArcheryEventCategoryDetail::select("archery_event_category_details.*", "archery_master_team_categories.type")
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
            ->where("archery_event_category_details.id", $category_id)
            ->first();

        if (!$category) {
            throw new BLoCException("category not found");
        }

        if (strtolower($category->type) == "individual") {
            return $this->resetScoringIndividu($elimination_id, $round);
        } else {
            return $this->resetScoringGroup($elimination_id, $round);
        }

        return -1;
    }

    private function resetScoringIndividu($elimination_id, $round)
    {
        // tangkap match berdasarkan round, match dan elimination
        $elimination_match = ArcheryEventEliminationMatch::select("archery_event_elimination_matches.*")
            ->join("archery_event_eliminations", "archery_event_eliminations.id", "=", "archery_event_elimination_matches.event_elimination_id")
            ->where("archery_event_elimination_matches.event_elimination_id", $elimination_id)
            ->where("archery_event_elimination_matches.round", $round)
            ->get();

        if ($elimination_match->count() < 1 || $elimination_match->count() > 2) {
            throw new BLoCException("match tidak valid");
        }

        foreach ($elimination_match as $key => $value) {
            if ($value->win == 1) {
                // reset pemenang dari match
                $value->win = 0;
                $value->save();

                // reset posisi pada round setelah round saat ini
                $next_match =  ArcheryEventEliminationMatch::where("event_elimination_id", $elimination_id)
                    ->where("round", ">", $round)
                    ->where("elimination_member_id", $value->elimination_member_id)
                    ->first();

                if (!$next_match) {
                    throw new BLoCException("next match tidak ditemukan");
                }
                $next_match->elimination_member_id = 0;
                $next_match->save();
            } else {
                // reset peringkat eliminasi di tabel elimination member
                $elimination_member = ArcheryEventEliminationMember::find($value->elimination_member_id);
                if (!$elimination_member) {
                    throw new BLoCException("elimination member not found");
                }

                $elimination_member->elimination_ranked = 0;
                $elimination_member->save();
            }
        }

        return "success";
    }

    private function resetScoringGroup($elimination_id, $round)
    {
        // tangkap match berdasarkan round, match dan elimination
        $elimination_group_match = ArcheryEventEliminationGroupMatch::select("archery_event_elimination_group_match.*")
            ->join("archery_event_elimination_group", "archery_event_elimination_group.id", "=", "archery_event_elimination_group_match.elimination_group_id")
            ->where("archery_event_elimination_group_match.elimination_group_id", $elimination_id)
            ->where("archery_event_elimination_group_match.round", $round)
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
                    ->first();

                if (!$next_match) {
                    throw new BLoCException("next match tidak ditemukan");
                }
                $next_match->group_team_id = 0;
                $next_match->save();
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
            "category_id" => "required"
        ];
    }
}
