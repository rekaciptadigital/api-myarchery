<?php

namespace App\BLoC\Web\EventElimination;

use App\Models\AdminRole;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryScoring;
use App\Models\User;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class CleanScoringQualification extends Retrieval
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
            $roles = AdminRole::where("admin_id", $admin->id)->where("event_id", $event->id)->where(function ($q) {
                $q->where("role_id", 5)->orWhere("role_id", 4);
            })->first();
            if (!$roles) {
                throw new BLoCException("forbiden");
            }
        }

        $members = ArcheryEventParticipant::select("archery_event_participant_members.*")->join("archery_event_participant_members", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")->where("archery_event_participants.event_category_id", $category_id)->where("archery_event_participants.status", 1)->get();

        foreach ($members as $key => $member) {
            $scoring_members =  ArcheryScoring::where("participant_member_id", $member->id)->where("type", 1)->orWhere("type", 11)->get();
            if ($scoring_members->count() > 0) {
                foreach ($scoring_members as $key => $score) {
                    $score->delete();
                }
            }
        }
        return "success";
    }

    protected function validation($parameters)
    {
        return ["category_id" => "required"];
    }
}
