<?php

namespace App\BLoC\App\Dashboard;

use App\Models\ArcheryEventParticipant;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;

class GetOnGoingEventDashboard extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get("event_id");
        $user = Auth::guard('app-api')->user();
        $archery_event_participants = ArcheryEventParticipant::select(
            "archery_master_competition_categories.label as competition",
            "archery_master_age_categories.label as age",
            "archery_master_distances.label as distance",
            "archery_master_team_categories.label as team",
            "archery_event_qualification_schedule_full_day.bud_rest_number",
            "archery_event_qualification_schedule_full_day.target_face"
        )
            ->join("archery_master_competition_categories", "archery_master_competition_categories.id", "=", "archery_event_participants.competition_category_id")
            ->join("archery_master_age_categories", "archery_master_age_categories.id", "=", "archery_event_participants.age_category_id")
            ->join("archery_master_distances", "archery_master_distances.id", "=", "archery_event_participants.distance_id")
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_participants.team_category_id")
            ->join("archery_event_participant_members", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->join("archery_event_qualification_schedule_full_day", "archery_event_qualification_schedule_full_day.participant_member_id", "=", "archery_event_participant_members.id")
            ->where("archery_event_participants.event_id", $event_id)
            ->where("archery_event_participants.status", 1)
            ->where("archery_event_participants.user_id", $user->id)
            ->orderBy("archery_master_competition_categories.label")
            ->get();

        $data = [];

        foreach ($archery_event_participants as $value_archery_event_participants) {
            $data[] = (object)[
                "budrest_number" => $value_archery_event_participants->bud_rest_number != 0 ? $value_archery_event_participants->bud_rest_number . $value_archery_event_participants->target_face : "",
                "competition" => $value_archery_event_participants->competition,
                "age" => $value_archery_event_participants->age,
                "distance" => $value_archery_event_participants->distance,
                "team" => $value_archery_event_participants->team,
            ];
        }

        return $data;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|exists:archery_events,id"
        ];
    }
}
