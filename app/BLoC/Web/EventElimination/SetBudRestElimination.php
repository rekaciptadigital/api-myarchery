<?php

namespace App\BLoC\Web\EventElimination;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventEliminationGroupMatch;
use App\Models\ArcheryEventEliminationGroupTeams;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventEliminationMember;
use App\Models\ArcheryMasterTeamCategory;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Validator;

class SetBudRestElimination extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $elimination_id = $parameters->get("elimination_id");
        $match = $parameters->get("match");
        $round = $parameters->get("round");
        $budrest_number = $parameters->get("budrest_number");
        $category_id = $parameters->get("category_id");
        $member_id = $parameters->get("member_id");
        $participant_id = $parameters->get("participant_id");

        $category = ArcheryEventCategoryDetail::find($category_id);
        if (!$category) {
            throw new BLoCException("category not found");
        }

        $team_category = ArcheryMasterTeamCategory::find($category->team_category_id);
        if (!$team_category) {
            throw new BLoCException("team category not found");
        }

        $bud_rest = 0;
        $target_face = "";

        // split budrest number dan target face
        $brn = preg_split('/(?<=[0-9])(?=[a-z]+)/i', $budrest_number);
        if (count($brn) == 1) {
            if (ctype_alpha($brn[0])) {
                throw new BLoCException("bantalan harus mengandung angka");
            }
            $bud_rest = $brn[0];
        } elseif (count($brn) == 2) {
            $bud_rest = $brn[0];
            $target_face = $brn[1];
        } else {
            throw new BLoCException("input invalid");
        }

        if (strtolower($team_category->type) == "team") {
            Validator::make($parameters->all(), [
                "participant_id" => "required|integer"
            ])->validate();
            return $this->setBudrestTeam($elimination_id, $match, $round, $bud_rest, $target_face, $participant_id);
        }

        if (strtolower($team_category->type) == "individual") {
            Validator::make($parameters->all(), [
                "member_id" => "required|integer"
            ])->validate();
            return $this->setBudrestIndividu($elimination_id, $match, $round, $bud_rest, $target_face, $member_id);
        }

        throw new BLoCException("set bud rest failed");
    }

    protected function validation($parameters)
    {
        return [
            "elimination_id" => "required|integer",
            "round" => "required|integer",
            "match" => "required|integer",
            "budrest_number" => "required|string",
            "category_id" => "required"
        ];
    }

    private function setBudrestIndividu($elimination_individu_id, $match, $round, $bud_rest, $target_face, $member_id)
    {
        $elimination = ArcheryEventElimination::find($elimination_individu_id);
        if (!$elimination) {
            throw new BLoCException("elimination data tidak ditemukan");
        }

        $elimination_member = ArcheryEventEliminationMember::where("member_id", $member_id)->first();
        if (!$elimination_member) {
            throw new BLoCException("elimination member not found");
        }

        $match = ArcheryEventEliminationMatch::where("event_elimination_id", $elimination_individu_id)
            ->where("match", $match)
            ->where("round", $round)
            ->where("elimination_member_id", $elimination_member->id)
            ->first();

        if (!$match) {
            throw new BLoCException("match not found");
        }

        $match->bud_rest = $bud_rest;
        $match->target_face = $target_face;
        $match->save();

        return "success";
    }

    private function setBudrestTeam($elimination_group_id, $match, $round, $bud_rest, $target_face, $participant_id)
    {
        $elimination_group = ArcheryEventEliminationGroup::find($elimination_group_id);
        if (!$elimination_group) {
            throw new BLoCException("elimination group data tidak ditemukan");
        }

        $elimination_group_team = ArcheryEventEliminationGroupTeams::where("participant_id", $participant_id)->first();
        if (!$elimination_group_team) {
            throw new BLoCException("elimination group team tidak ditemukan");
        }

        $match = ArcheryEventEliminationGroupMatch::where("elimination_group_id", $elimination_group_id)
            ->where("match", $match)
            ->where("round", $round)
            ->where("group_team_id", $elimination_group_team->id)
            ->first();

        if (!$match) {
            throw new BLoCException("match team not found");
        }

        $match->bud_rest = $bud_rest;
        $match->target_face = $target_face;
        $match->save();

        return "success";
    }
}
