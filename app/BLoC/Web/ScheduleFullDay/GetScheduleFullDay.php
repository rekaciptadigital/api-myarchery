<?php

namespace App\BLoC\Web\ScheduleFullDay;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetScheduleFullDay extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        // param
        $date = $parameters->get("date");
        $name = $parameters->get("name");
        $event_id = $parameters->get("event_id");
        $admin = Auth::user();

        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException('you are not owner this event');
        }

        $schedule_member_query = ArcheryEventQualificationScheduleFullDay::select(
            "archery_event_qualification_schedule_full_day.*",
            "archery_event_qualification_time.category_detail_id as category_id",
            "users.name as name",
            "archery_clubs.name as club_name"
        )
            ->join("archery_event_qualification_time", "archery_event_qualification_time.id", "=", "archery_event_qualification_schedule_full_day.qalification_time_id")
            ->join("archery_event_participant_members", "archery_event_participant_members.id", "=", "archery_event_qualification_schedule_full_day.participant_member_id")
            ->join("users", "users.id", "=", "archery_event_participant_members.user_id")
            ->join("archery_event_participants", "archery_event_participants.id", "=", "archery_event_participant_members.archery_event_participant_id")
            ->join("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id")
            ->where("archery_event_participants.event_id", $event_id)
            ->whereDate("event_start_datetime", $date);

        $schedule_member_query->when($name, function ($query) use ($name) {
            return $query->whereRaw("users.name LIKE ?", ["%" . $name . "%"]);
        });

        $schedule_member_collection = $schedule_member_query->orDerBy("archery_event_qualification_schedule_full_day.bud_rest_number")
            ->orderBy("archery_event_qualification_schedule_full_day.target_face")
            ->get();

        $output = [];
        $output["date"] = $date;
        $output["category_budrest"] = null;

        if ($schedule_member_collection->count() > 0) {
            foreach ($schedule_member_collection as $schedule) {
                $category = ArcheryEventCategoryDetail::find($schedule->category_id);
                if (!$category) {
                    throw new BLoCException("category tidak tersedia");
                }

                $output["category_budrest"][$category->id][] = [
                    "category_id" => $category->id,
                    "label_category" => $category->label_category,
                    "bud_rest_number" => $schedule->bud_rest_number . "" . $schedule->target_face,
                    "name" => $schedule->name,
                    "club_name" => $schedule->club_name
                ];
            }
        }

        return $output;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|integer",
            "date" => "required"
        ];
    }
}
