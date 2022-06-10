<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryScoring;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventEliminationGroupMatch;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryMasterTeamCategory;
use App\Models\ArcheryScoringEliminationGroup;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Validator;

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
        $participant_id = $parameters->get("participant_id");
        $admin_total = $parameters->get("admin_total");
        $category_id = $parameters->get("category_id");

        // periksa category
        $category = ArcheryEventCategoryDetail::find($category_id);
        if (!$category) {
            throw new BLoCException("category not found");
        }

        $team_category = ArcheryMasterTeamCategory::find($category->team_category_id);
        if (!$team_category) {
            throw new BLoCException("team category not found");
        }


        if (strtolower($team_category->type) == "team") {
            Validator::make($parameters->all(), ["participant_id" => "required"])->validate();
            return $this->setAdminTotalTeam($elimination_id, $round, $match, $participant_id, $admin_total);
        }

        if (strtolower($team_category->type) == "individual") {
            Validator::make($parameters->all(), ["member_id" => "required"])->validate();
            return $this->setAdminTotalIndividu($elimination_id, $round, $match, $member_id, $admin_total);
        }

        throw new BLoCException("gagal set admin total");
    }

    protected function validation($parameters)
    {
        return [
            "elimination_id" => "required",
            "round" => "required",
            "match" => "required",
            "admin_total" => "required",
            "category_id" => "required"
        ];
    }

    private function setAdminTotalIndividu($elimination_id, $round, $match, $member_id, $admin_total)
    {
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

            $scooring = new ArcheryScoring;
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

    private function setAdminTotalTeam($elimination_id, $round, $match, $participant_id, $admin_total)
    {
        // 2. pastikan elimination id tsb terdapat di db
        $elimination = ArcheryEventEliminationGroup::find($elimination_id);
        if (!$elimination) {
            throw new BLoCException("data eliminasi tidak ditemukan");
        }
        // 3. cari di tabel elimination match yang elimination, match, member_id dan round sesuai
        $elimination_match = ArcheryEventEliminationGroupMatch::select(
            "archery_event_elimination_group_teams.participant_id",
            "archery_event_elimination_group_match.*"
        )
            ->join("archery_event_elimination_group_teams", "archery_event_elimination_group_match.group_team_id", "=", "archery_event_elimination_group_teams.id")
            ->where("archery_event_elimination_group_match.elimination_group_id", $elimination_id)
            ->where("archery_event_elimination_group_match.round", $round)
            ->where("archery_event_elimination_group_match.match", $match)
            ->where("archery_event_elimination_group_teams.participant_id", $participant_id)
            ->first();

        if (!$elimination_match) {
            throw new BLoCException("elimination group match tidak valid");
        }
        // 4. cari di tabel skoring dengan id member dari table match dan type 2 dan pastikan ketersediaannya dan item_id yang sesuai dengan elimination match
        $scooring = ArcheryScoringEliminationGroup::where("participant_id", $elimination_match->participant_id)
            ->where("elimination_match_group_id", $elimination_match->id)
            ->first();

        // 5. jika belum tersedia insertkan hal yang mandatory pada tabel archery scoring beserta admin total ke table scooring
        if (!$scooring) {
            if ($elimination->elimination_scoring_type == 0) {
                throw new BLoCException("elimination scooring type belum ditentukan");
            }

            if ($elimination->elimination_scoring_type == 1) {
                $scoring_detail = ArcheryScoringEliminationGroup::makeEliminationScoringTypePointFormat();
            }

            if ($elimination->elimination_scoring_type == 2) {
                $scoring_detail = ArcheryScoringEliminationGroup::makeEliminationScoringTypeTotalFormat();
            }

            $scooring = new ArcheryScoringEliminationGroup;
            $scooring->elimination_match_group_id = $elimination_match->id;
            $scooring->participant_id = $participant_id;
            $scooring->result = 0;
            $scooring->scoring_detail = json_encode($scoring_detail);
            $scooring->scoring_log = json_encode($elimination_match);
        }
        // jika tersedia update hanya field admin total
        $scooring->admin_total = $admin_total;
        $scooring->save();

        return $scooring;
    }
}
