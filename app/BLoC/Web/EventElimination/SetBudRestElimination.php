<?php

namespace App\BLoC\Web\EventElimination;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventEliminationGroupMatch;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventEliminationSchedule;
use App\Models\ArcheryMasterTeamCategory;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

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

        $category = ArcheryEventCategoryDetail::find($category_id);
        if (!$category) {
            throw new BLoCException("category not found");
        }

        $team_category = ArcheryMasterTeamCategory::find($category->team_category_id);
        if (!$team_category) {
            throw new BLoCException("team category not found");
        }

        // split budrest number dan target face
        $brn = preg_split('/(?<=[0-9])(?=[a-z]+)/i', $budrest_number);
        if (count($brn) != 2) {
            throw new BLoCException("bantalan harus terdiri dari huruf dan angka");
        }
        $bud_rest = $brn[0];
        $target_face = $brn[1];

        if (strtolower($team_category->type) == "team") {
            return $this->setBudrestTeam($elimination_id, $match, $round, $bud_rest, $target_face);
        }

        if (strtolower($team_category->type) == "individual") {
            return $this->setBudrestIndividu($elimination_id, $match, $round, $bud_rest, $target_face);
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

    private function setBudrestIndividu($elimination_individu_id, $match, $round, $bud_rest, $target_face)
    {
        $elimination = ArcheryEventElimination::find($elimination_individu_id);
        if (!$elimination) {
            throw new BLoCException("elimination data tidak ditemukan");
        }

        $match = ArcheryEventEliminationMatch::where("event_elimination_id", $elimination_individu_id)
            ->where("match", $match)
            ->where("round", $round)
            ->get();

        if ($match->count() != 2) {
            throw new BLoCException("match invalid");
        }


        foreach ($match as $key => $value) {
            $value->bud_rest = $bud_rest;
            $value->target_face = $target_face;
            $value->save();
        }

        return "success";
    }

    private function setBudrestTeam($elimination_group_id, $match, $round, $bud_rest, $target_face)
    {
        $elimination_group = ArcheryEventEliminationGroup::find($elimination_group_id);
        if (!$elimination_group) {
            throw new BLoCException("elimination group data tidak ditemukan");
        }

        $match = ArcheryEventEliminationGroupMatch::where("elimination_group_id", $elimination_group_id)
            ->where("match", $match)
            ->where("round", $round)
            ->get();

        if ($match->count() != 2) {
            throw new BLoCException("match invalid");
        }

        foreach ($match as $key => $value) {
            $value->bud_rest = $bud_rest;
            $value->target_face = $target_face;
            $value->save();
        }

        return "success";
    }
}
