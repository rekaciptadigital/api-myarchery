<?php

namespace App\BLoC\Web\EventElimination;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventEliminationGroupMatch;
use App\Models\ArcheryEventEliminationGroupMemberTeam;
use App\Models\ArcheryEventEliminationGroupTeams;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventEliminationMember;
use App\Models\ArcheryMasterTeamCategory;
use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryScoring;
use App\Models\ArcheryScoringEliminationGroup;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class CleanEliminationMatch extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $category_id = $parameters->get("category_id");
        $category = ArcheryEventCategoryDetail::find($category_id);
        if (!$category_id) {
            throw new BLoCException("category not found");
        }

        $event = ArcheryEvent::find($category->event_id);
        if (!$event) {
            throw new BLoCException("event not found");
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException("forbiden");
        }

        $team_category = ArcheryMasterTeamCategory::find($category->team_category_id);
        if (!$team_category) {
            throw new BLoCException("team category not found");
        }

        if (strtolower($team_category->type) == "team") {
            return $this->cleanEliminationMatchTeam($category_id);
        }

        if (strtolower($team_category->type) == "individual") {
            return $this->cleanEliminationMatch($category_id);
        }

        throw new BLoCException("failed");
    }

    private function cleanEliminationMatch($category_id)
    {
        $elimination = ArcheryEventElimination::where('event_category_id', $category_id)->first();
        if (!$elimination) {
            throw new BLoCException("elimination tidak ditemukan");
        }

        $list_match = ArcheryEventEliminationMatch::where("event_elimination_id", $elimination->id)->get();
        foreach ($list_match as $value) {
            if ($value->elimination_member_id != 0) {
                $member = ArcheryEventEliminationMember::find($value->elimination_member_id);
                if ($member) {
                    $member->delete();
                }
            }
            $scoring = ArcheryScoring::where("item_id", $value->id)->where("item_value", "archery_event_elimination_matches")->first();
            if ($scoring) {
                $scoring->delete();
            }

            $value->delete();
        }

        $elimination->delete();
        return "success";
    }

    private function cleanEliminationMatchTeam($category_id)
    {
        $elimination = ArcheryEventEliminationGroup::where('category_id', $category_id)->first();
        if (!$elimination) {
            throw new BLoCException("elimination group tidak ditemukan");
        }

        $list_match = ArcheryEventEliminationGroupMatch::where("elimination_group_id", $elimination->id)->get();
        foreach ($list_match as $value) {
            if ($value->group_team_id != 0) {
                $member = ArcheryEventEliminationGroupTeams::find($value->group_team_id);
                if ($member) {
                    $member_team = ArcheryEventEliminationGroupMemberTeam::where("participant_id", $member->participant_id)->get();
                    if ($member_team->count() > 0) {
                        foreach ($member_team as $key => $mt) {
                            $mt->delete();
                        }
                    }
                    $member->delete();
                }
            }
            $scoring = ArcheryScoringEliminationGroup::where("elimination_match_group_id", $value->id)->first();
            if ($scoring) {
                $scoring->delete();
            }

            $value->delete();
        }

        $elimination->delete();
        return "success";
    }

    protected function validation($parameters)
    {
        return ["category_id" => "required"];
    }
}
