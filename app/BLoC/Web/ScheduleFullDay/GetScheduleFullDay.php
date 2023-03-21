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
        $search = "%" . $parameters->get("search") . "%";
        $event_id = $parameters->get("event_id");
        $admin = Auth::user();

        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException('you are not owner this event');
        }

        $parent_classfification_id = $event->parent_classification;

        if ($parent_classfification_id == 0) {
            throw new BLoCException("parent calassification_id invalid");
        }

        $select_classification_query = "archery_clubs.name as classification_name";
        $table_for_search = "archery_clubs.name";

        if ($parent_classfification_id == 2) { // jika mewakili negara
            $table_for_search = "countries.name";
            $select_classification_query = "countries.name as classification_name";
        }

        if ($parent_classfification_id == 3) { // jika mewakili provinsi
            if ($event->classification_country_id == 102) {
                $table_for_search = "provinces.name";
                $select_classification_query = "provinces.name as classification_name";
            } else {
                $table_for_search = "states.name";
                $select_classification_query = "states.name as classification_name";
            }
        }

        if ($parent_classfification_id == 4) { // jika mewakili kota
            if ($event->classification_country_id == 102) {
                $table_for_search = "cities.name";
                $select_classification_query = "cities.name as classification_name";
            } else {
                $table_for_search = "cities_of_countries.name";
                $select_classification_query = "cities_of_countries.name as classification_name";
            }
        }

        if ($parent_classfification_id == 6) { // jika berasal dari settingan admin
            $table_for_search = "children_classification_members.title";
            $select_classification_query = "children_classification_members.title as classification_name";
        }

        $schedule_member_query = ArcheryEventQualificationScheduleFullDay::select(
            "archery_event_qualification_schedule_full_day.*",
            "archery_event_qualification_time.category_detail_id as category_id",
            "users.name as name",
            "archery_event_participants.id as participant_id",
            $select_classification_query
        )
            ->join("archery_event_qualification_time", "archery_event_qualification_time.id", "=", "archery_event_qualification_schedule_full_day.qalification_time_id")
            ->join("archery_event_participant_members", "archery_event_participant_members.id", "=", "archery_event_qualification_schedule_full_day.participant_member_id")
            ->join("users", "users.id", "=", "archery_event_participant_members.user_id")
            ->join("archery_event_participants", "archery_event_participants.id", "=", "archery_event_participant_members.archery_event_participant_id");

        if ($parent_classfification_id == 1) { // jika mewakili club
            $schedule_member_query = $schedule_member_query->leftJoin("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id");
        }

        if ($parent_classfification_id == 2) { // jika mewakili negara
            $schedule_member_query = $schedule_member_query->join("countries", "countries.id", "=", "archery_event_participants.classification_country_id");
        }

        if ($parent_classfification_id == 3) { // jika mewakili provinsi
            if ($event->classification_country_id == 102) {
                $schedule_member_query = $schedule_member_query->join("provinces", "provinces.id", "=", "archery_event_participants.classification_province_id");
            } else {
                $schedule_member_query = $schedule_member_query->join("states", "states.id", "=", "archery_event_participants.classification_province_id");
            }
        }

        if ($parent_classfification_id == 4) { // jika mewakili kota
            if ($event->classification_country_id == 102) {
                $schedule_member_query = $schedule_member_query->join("cities", "cities.id", "=", "archery_event_participants.city_id");
            } else {
                $schedule_member_query = $schedule_member_query->join("cities_of_countries", "cities_of_countries.id", "=", "archery_event_participants.city_id");
            }
        }

        if ($parent_classfification_id == 6) { // jika berasal dari settingan admin
            $schedule_member_query = $schedule_member_query->join("children_classification_members", "children_classification_members.id", "=", "archery_event_participants.children_classification_id");
        }

        $schedule_member_query = $schedule_member_query->where("archery_event_participants.event_id", $event_id)
            ->where("archery_event_participants.status", 1)
            ->whereDate("event_start_datetime", $date);

        $schedule_member_query->when($search, function ($query) use ($search, $table_for_search) {
            return $query->where(function ($q) use ($search, $table_for_search) {
                return $q->whereRaw("users.name LIKE ?", [$search])
                    ->orWhereRaw($table_for_search . " Like ?", [$search]);
            });
        });

        $schedule_member_collection = $schedule_member_query->orderBy("archery_event_qualification_schedule_full_day.bud_rest_number")
            ->orderBy("archery_event_qualification_schedule_full_day.target_face")
            ->get();

        $output = [];
        $output["date"] = $date;
        $output["category_budrest"] = null;

        if ($schedule_member_collection->count() > 0) {
            foreach ($schedule_member_collection as $schedule) {
                $category = ArcheryEventCategoryDetail::select("archery_event_category_details.*")
                    ->join("archery_events", "archery_events.id", "=", "archery_event_category_details.event_id")
                    ->where("archery_event_category_details.id", $schedule->category_id)
                    ->first();
                if (!$category) {
                    throw new BLoCException("category tidak tersedia");
                }

                $output["category_budrest"][$category->id][] = [
                    "schedule_full_day_id" => $schedule->id,
                    "category_id" => $category->id,
                    "event_id" => $category->event_id,
                    "label_category" => $category->label_category,
                    "bud_rest_number" => $schedule->bud_rest_number === 0 ? "" : $schedule->bud_rest_number . "" . $schedule->target_face,
                    "name" => $schedule->name,
                    "classification_name" => $schedule->classification_name,
                    "participant_id" => $schedule->participant_id,
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
