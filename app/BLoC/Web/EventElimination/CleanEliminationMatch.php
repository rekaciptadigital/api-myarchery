<?php

namespace App\BLoC\Web\EventElimination;

use App\Models\AdminRole;
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
use App\Models\UrlReport;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

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

        $data = Redis::get($category->id . "_LIVE_SCORE");
        if ($data) {
            Redis::del($category->id . "_LIVE_SCORE");
        }

        $event = ArcheryEvent::find($category->event_id);
        if (!$event) {
            throw new BLoCException("event not found");
        }

        UrlReport::removeAllUrlReport($event->id);

        if ($event->admin_id != $admin->id) {
            $roles = AdminRole::where("admin_id", $admin->id)->where("event_id", $event->id)->where(function ($q) {
                $q->where("role_id", 5)->orWhere("role_id", 4);
            })->first();
            if (!$roles) {
                throw new BLoCException("forbiden");
            }
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
                // return $value;
                $member = ArcheryEventEliminationMember::find($value->elimination_member_id);
                if ($member) {
                    $scoring = ArcheryScoring::where("type", 2)->where("item_id", $value->id)
                        ->where("participant_member_id", $member->member_id)
                        ->where("item_value", "archery_event_elimination_matches")
                        ->first();

                    if ($scoring) {
                        $scoring->delete();
                    }

                    $member->delete();
                }
            }
            $scoring = ArcheryScoring::where("type", 2)->where("item_id", $value->id)->where("item_value", "archery_event_elimination_matches")->first();
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
                $group_team = ArcheryEventEliminationGroupTeams::find($value->group_team_id);
                if ($group_team) {
                    $member_team = ArcheryEventEliminationGroupMemberTeam::where("participant_id", $group_team->participant_id)->get();

                    foreach ($member_team as $mt) {
                        $mt->delete();
                    }

                    $group_team->delete();
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
