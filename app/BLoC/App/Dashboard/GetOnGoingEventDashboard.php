<?php

namespace App\BLoC\App\Dashboard;

use App\Models\ArcheryEvent;
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
        $user = Auth::guard('app-api')->user();
        $data = null;
        $archery_event = ArcheryEvent::select("archery_events.*")
            ->join("archery_event_participants", "archery_event_participants.event_id", "=", "archery_events.id")
            ->where("archery_events.event_start_datetime", ">", date("Y-m-d H:i:s", time()))
            ->where("archery_event_participants.user_id", $user->id)
            ->where("archery_event_participants.status", 1)
            ->orderBy("archery_events.event_start_datetime")
            ->first();

        if (!$archery_event) {
            return $data;
        }

        $archery_event_participants = ArcheryEventParticipant::select(
            "archery_master_competition_categories.label as competition",
            "archery_master_age_categories.label as age",
            "archery_master_distances.label as distance",
            "archery_master_team_categories.label as team",
            "archery_event_qualification_schedule_full_day.bud_rest_number",
            "archery_event_qualification_schedule_full_day.target_face",
            "archery_event_participants.event_category_id as category_id"
        )
            ->join("archery_master_competition_categories", "archery_master_competition_categories.id", "=", "archery_event_participants.competition_category_id")
            ->join("archery_master_age_categories", "archery_master_age_categories.id", "=", "archery_event_participants.age_category_id")
            ->join("archery_master_distances", "archery_master_distances.id", "=", "archery_event_participants.distance_id")
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_participants.team_category_id")
            ->join("archery_event_participant_members", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->join("archery_event_qualification_schedule_full_day", "archery_event_qualification_schedule_full_day.participant_member_id", "=", "archery_event_participant_members.id")
            ->where("archery_event_participants.event_id", $archery_event->id)
            ->where("archery_event_participants.status", 1)
            ->where("archery_event_participants.user_id", $user->id)
            ->orderBy("archery_master_competition_categories.label")
            ->get();

        if ($archery_event_participants->count() > 0) {
            $data = (object)[];
            $data->detail_event = (object)[
                "id" => $archery_event->id,
                "name" => $archery_event->event_name,
                "start_event" => $archery_event->event_start_datetime,
                "end_event" => $archery_event->event_end_datetime,
                "poster" => $archery_event->poster,
                "location" => $archery_event->location
            ];

            foreach ($archery_event_participants as $value_archery_event_participants) {
                $data->list_category[] = (object)[
                    "id" => $value_archery_event_participants->category_id,
                    "budrest_number" => $value_archery_event_participants->bud_rest_number != 0 ? $value_archery_event_participants->bud_rest_number . $value_archery_event_participants->target_face : "",
                    "competition" => $value_archery_event_participants->competition,
                    "age" => $value_archery_event_participants->age,
                    "distance" => $value_archery_event_participants->distance,
                    "team" => $value_archery_event_participants->team,
                ];
            }
        }

        return $data;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
