<?php

namespace App\BLoC\Web\ScheduleFullDay;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use App\Exports\ArcheryEventBudrestExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class DownloadMemberBudrest extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {

        $event_id = $parameters->get('event_id');
        $date = $parameters->get("date");
        $admin = Auth::user();

        $event = ArcheryEvent::find($event_id);

        if ($event->admin_id != $admin->id) {
            throw new BLoCException('you are not owner this event');
        }

        $filename = '/report-budrest/' . $event_id . '/' . $event->event_name . '_BUDREST_' . $date . '.xlsx';


        $data = $this->getScheduleFullday($event, $date);
        if ($data["category_budrest"] == null) {
            throw new BLoCException("jadwal tidak tersedia");
        }
        $file_excel = new ArcheryEventBudrestExport($data);
        Excel::store($file_excel, $filename, 'public');

        $destinationPath = Storage::url($filename);
        $file_path = env('STOREG_PUBLIC_DOMAIN') . $destinationPath;

        return $file_path;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|integer|exists:archery_events,id",
            "date" => "required"
        ];
    }

    protected function getScheduleFullday(ArcheryEvent $event, $date)
    {
        $schedule_member_query = ArcheryEventQualificationScheduleFullDay::select(
            "archery_event_qualification_schedule_full_day.*",
            "archery_event_qualification_time.category_detail_id as category_id",
            "users.name as name",
            "archery_event_participants.club_id as club_id",
            "archery_clubs.name as club_name",
            "archery_event_participants.classification_country_id",
            "countries.name as country_name",
            "archery_event_participants.classification_province_id",
            $event->classification_country_id == 102 ? "provinces.name as province_name" : "states.name as province_name",
            "archery_event_participants.city_id",
            $event->classification_country_id == 102 ? "cities.name as city_name" : "cities_of_countries.name as city_name",
            "archery_event_participants.children_classification_id",
            "children_classification_members.title as children_classification_members_name",
            "archery_events.with_contingent"
        )
            ->join("archery_event_qualification_time", "archery_event_qualification_time.id", "=", "archery_event_qualification_schedule_full_day.qalification_time_id")
            ->join("archery_event_participant_members", "archery_event_participant_members.id", "=", "archery_event_qualification_schedule_full_day.participant_member_id")
            ->join("users", "users.id", "=", "archery_event_participant_members.user_id")
            ->join("archery_event_participants", "archery_event_participants.id", "=", "archery_event_participant_members.archery_event_participant_id")
            ->join("archery_events", "archery_events.id", "=", "archery_event_participants.event_id");
        // jika mewakili negara
        $schedule_member_query = $schedule_member_query->leftJoin("countries", "countries.id", "=", "archery_event_participants.classification_country_id");

        // jika mewakili provinsi
        if ($event->classification_country_id == 102) {
            $schedule_member_query = $schedule_member_query->leftJoin("provinces", "provinces.id", "=", "archery_event_participants.classification_province_id");
        } else {
            $schedule_member_query = $schedule_member_query->leftJoin("states", "states.id", "=", "archery_event_participants.classification_province_id");
        }

        // jika mewakili kota
        if ($event->classification_country_id == 102) {
            $schedule_member_query = $schedule_member_query->leftJoin("cities", "cities.id", "=", "archery_event_participants.city_id");
        } else {
            $schedule_member_query = $schedule_member_query->leftJoin("cities_of_countries", "cities_of_countries.id", "=", "archery_event_participants.city_id");
        }

        $schedule_member_query = $schedule_member_query->leftJoin("children_classification_members", "children_classification_members.id", "=", "archery_event_participants.children_classification_id");

        $schedule_member_query->leftJoin(
            "archery_clubs",
            "archery_clubs.id",
            "=",
            "archery_event_participants.club_id"
        );

        $schedule_member_query = $schedule_member_query->where("archery_event_participants.event_id", $event->id)
            ->whereDate("archery_event_qualification_time.event_start_datetime", $date);

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
                    "club_name" => $schedule["club_name"],
                    "classification_country_id" => $schedule["classification_country_id"],
                    "country_name" => $schedule["country_name"],
                    "classification_province_id" => $schedule["classification_province_id"],
                    "province_name" => $schedule["province_name"],
                    "city_id" => $schedule["city_id"],
                    "city_name" => $schedule["city_name"],
                    "children_classification_id" => $schedule["children_classification_id"],
                    "children_classification_members_name" => $schedule["children_classification_members_name"],
                    "parent_classification_type" => $event->parent_classification,
                    "with_contingent" => $schedule->with_contingent
                ];
            }
        }

        return $output;
    }
}
