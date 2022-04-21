<?php

namespace App\BLoC\Web\ScheduleFullDay;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\BudRest;
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
            "archery_clubs.name as club_name",
            "archery_clubs.id as club_id"
        )
            ->join("archery_event_qualification_time", "archery_event_qualification_time.id", "=", "archery_event_qualification_schedule_full_day.qalification_time_id")
            ->join("archery_event_participant_members", "archery_event_participant_members.id", "=", "archery_event_qualification_schedule_full_day.participant_member_id")
            ->join("users", "users.id", "=", "archery_event_participant_members.user_id")
            ->join("archery_event_participants", "archery_event_participants.id", "=", "archery_event_participant_members.archery_event_participant_id")
            ->leftJoin("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id")
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
                    "schedule_full_day_id" => $schedule->id,
                    "category_id" => $category->id,
                    "label_category" => $category->label_category,
                    "bud_rest_number" => $schedule->bud_rest_number === 0 ? "" : $schedule->bud_rest_number . "" . $schedule->target_face,
                    "name" => $schedule->name,
                    "club_id" => $schedule->club_id,
                    "club_name" => $schedule->club_name
                ];
            }
        }

        return $output;
    }

    // protected function process($parameters)
    // {
    //     // param
    //     $date = $parameters->get("date");
    //     $event_id = $parameters->get("event_id");
    //     $admin = Auth::user();

    //     $event = ArcheryEvent::find($event_id);
    //     if (!$event) {
    //         throw new BLoCException("event tidak ditemukan");
    //     }

    //     if ($event->admin_id != $admin->id) {
    //         throw new BLoCException('you are not owner this event');
    //     }

    //     $member_not_have_budrest = ArcheryEventQualificationScheduleFullDay::select(
    //         "archery_event_qualification_schedule_full_day.*",
    //         "archery_event_qualification_time.category_detail_id as category_id",
    //         "users.name as name",
    //         "archery_clubs.name as club_name",
    //         "archery_clubs.id as club_id"
    //     )
    //         ->join("archery_event_qualification_time", "archery_event_qualification_time.id", "=", "archery_event_qualification_schedule_full_day.qalification_time_id")
    //         ->join("archery_event_participant_members", "archery_event_participant_members.id", "=", "archery_event_qualification_schedule_full_day.participant_member_id")
    //         ->join("users", "users.id", "=", "archery_event_participant_members.user_id")
    //         ->join("archery_event_participants", "archery_event_participants.id", "=", "archery_event_participant_members.archery_event_participant_id")
    //         ->join("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id")
    //         ->where("archery_event_participants.event_id", $event_id)
    //         ->whereDate("event_start_datetime", $date)
    //         ->where("archery_event_qualification_schedule_full_day.bud_rest_number", 0)
    //         ->where("archery_event_qualification_schedule_full_day.target_face", "")
    //         ->get();


    //     $output = [];
    //     $output["date"] = $date;
    //     $output["category_budrest"] = null;
    //     $response = [];

    //     if ($member_not_have_budrest->count() > 0) {
    //         foreach ($member_not_have_budrest as $mnhb) {
    //             $category = ArcheryEventCategoryDetail::find($mnhb->category_id);
    //             if (!$category) {
    //                 throw new BLoCException("category tidak tersedia");
    //             }
    //             $response["schedule_full_day_id"] = $mnhb->schedule_id;
    //             $response["category_id"] = $category->id;
    //             $response["label_category"] = $category->label_category;
    //             $response["bud_rest_number"] = "";
    //             $response["name"] = $mnhb->name;
    //             $response["club_id"] = $mnhb->club_id;
    //             $response["club_name"] = $mnhb->club_name;

    //             $output["category_budrest"][$category->id][] = $response;
    //         }
    //     }

    //     $bud_rest = BudRest::select("bud_rest.*")
    //         ->join("archery_event_category_details", "archery_event_category_details.id", "=", "bud_rest.archery_event_category_id")
    //         ->join("archery_event_qualification_time", "archery_event_qualification_time.category_detail_id", "=", "bud_rest.archery_event_category_id")
    //         ->where("archery_event_category_details.event_id", $event_id)
    //         ->whereDate("event_start_datetime", $date)
    //         ->get();


    //     $target_face = ["A", "C", "B", "D", "E", "F"];
    //     if ($bud_rest->count() > 0) {
    //         foreach ($bud_rest as $value) {
    //             for ($i = $value->bud_rest_start; $i <= $value->bud_rest_end; $i++) {
    //                 for ($j = $target_face[0]; $j <= $target_face[$value->target_face - 1]; $j++) {
    //                     $label = $i . "" . $j;

    //                     error_log($label);

    //                     $schedule_full_day =  ArcheryEventQualificationScheduleFullDay::select("archery_event_qualification_schedule_full_day.bud_rest_number", "archery_event_qualification_schedule_full_day.target_face", "archery_event_qualification_time.category_detail_id as category_id", "users.name", "archery_clubs.id", "archery_clubs.name")
    //                         ->join("archery_event_qualification_time", "archery_event_qualification_time.id", "=", "archery_event_qualification_schedule_full_day.qalification_time_id")
    //                         ->join("archery_event_participant_members", "archery_event_participant_members.id", "=", "archery_event_qualification_schedule_full_day.participant_member_id")
    //                         ->join("users", "users.id", "=", "archery_event_participant_members.user_id")
    //                         ->join("archery_event_participants", "archery_event_participants.id", "=", "archery_event_participant_members.archery_event_participant_id")
    //                         ->join("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id")
    //                         ->where("archery_event_qualification_time.category_detail_id", $value->archery_event_category_id)
    //                         ->where("archery_event_qualification_schedule_full_day.bud_rest_number", $i)
    //                         ->where("archery_event_qualification_schedule_full_day.target_face", $j)
    //                         ->first();

    //                     if (!$schedule_full_day) {
    //                         continue;
    //                     }

    //                     $category = ArcheryEventCategoryDetail::find($schedule_full_day->category_id);
    //                     if (!$category) {
    //                         throw new BLoCException("category tidak tersedia");
    //                     }

    //                     $response["schedule_full_day_id"] = null;
    //                     $response["category_id"] = $category->id;
    //                     $response["label_category"] = $category->label_category;
    //                     $response["bud_rest_number"] = $label;
    //                     $response["name"] = null;
    //                     $response["club_id"] = null;
    //                     $response["club_name"] = null;
    //                     if ($schedule_full_day) {
    //                         $response["schedule_full_day_id"] = $schedule_full_day->id;
    //                         $response["name"] = $schedule_full_day->name;
    //                         $response["club_id"] = $schedule_full_day->club_id;
    //                         $response["club_name"] = $schedule_full_day->club_name;
    //                     }
    //                     $output["category_budrest"][$category->id][] = $response;
    //                 }
    //             }
    //         }
    //     }
    //     return $output;
    // }


    protected function validation($parameters)
    {
        return [
            "event_id" => "required|integer",
            "date" => "required"
        ];
    }
}
