<?php

namespace App\BLoC\Web\BudRest;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\BudRest;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetListBudRestV2 extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        // param
        $event_id = $parameters->get("event_id");
        $event = ArcheryEvent::find($event_id);
        $date = $parameters->get("date");
        $category_id = $parameters->get("category_id");

        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException("FORBIDEN");
        }

        $bud_rest = BudRest::select("bud_rest.*")->join("archery_event_category_details", "archery_event_category_details.id", "=", "bud_rest.archery_event_category_id")
            ->join("archery_event_qualification_time", "archery_event_qualification_time.category_detail_id", "=", "bud_rest.archery_event_category_id")
            ->where("archery_event_category_details.event_id", $event_id)
            // ->whereDate("event_start_datetime", $date)
            ->where("archery_event_category_details.id", $category_id)
            ->get();

        $response = [];
        $output = [];

        $target_face = ["A", "B", "C", "D", "E", "F"];
        if ($bud_rest->count() > 0) {
            foreach ($bud_rest as $value) {
                for ($i = $value->bud_rest_start; $i <= $value->bud_rest_end; $i++) {
                    for ($j = $target_face[0]; $j <= $target_face[$value->target_face - 1]; $j++) {
                        $label = $i . "" . $j;
                        $response["label"] = $label;

                        $schedule_full_day =  ArcheryEventQualificationScheduleFullDay::select("archery_event_qualification_schedule_full_day.bud_rest_number", "archery_event_qualification_schedule_full_day.target_face")
                            ->join("archery_event_qualification_time", "archery_event_qualification_time.id", "=", "archery_event_qualification_schedule_full_day.qalification_time_id")
                            ->where("archery_event_qualification_time.category_detail_id", $value->archery_event_category_id)
                            ->where("archery_event_qualification_schedule_full_day.bud_rest_number", $i)
                            ->where("archery_event_qualification_schedule_full_day.target_face", $j)
                            ->first();

                        $is_empty = 1;
                        if ($schedule_full_day) {
                            $is_empty = 0;
                        }

                        $response["is_empty"] = $is_empty;
                        array_push($output, $response);
                    }
                }
            }
        }

        return $output;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required",
            "category_id" => "required"
        ];
    }
}
