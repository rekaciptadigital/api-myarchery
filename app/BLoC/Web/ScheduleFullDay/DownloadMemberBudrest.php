<?php

namespace App\BLoC\Web\ScheduleFullDay;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\BudRest;
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
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException('you are not owner this event');
        }
        
        $filename = '/report-budrest/'.$event_id.'/'.$event->event_name.'_BUDREST_'.$date.'.xlsx';


        $data = $this->getScheduleFullday($event_id, $date);
        $file_excel = new ArcheryEventBudrestExport($data);
        $download= Excel::store($file_excel, $filename, 'public');
       
        $destinationPath = Storage::url($filename);
        $file_path = env('STOREG_PUBLIC_DOMAIN').$destinationPath;

        return $file_path;

    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|integer",
            "date" => "required"
        ];
    }

    protected function getScheduleFullday($event_id, $date)
    {
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
}
