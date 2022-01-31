<?php

namespace App\BLoC\Web\ArcheryEventQualificationTime;

use App\Models\ArcheryEventQualificationTime;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;

class AddArcheryEventQualificationTime extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $qualification_times = $parameters->get('qualification_time', []);
        foreach ($qualification_times as $qualification_time) {
            $category_detail_id=$qualification_time['category_detail_id'];

            $archery_event_qualification_time = ArcheryEventQualificationTime::where('category_detail_id', $category_detail_id)->first();
            if (!$archery_event_qualification_time) {
                $archery_event_qualification_time = new ArcheryEventQualificationTime();
                $archery_event_qualification_time->category_detail_id = $qualification_time['category_detail_id'];
                $archery_event_qualification_time->event_start_datetime =  $qualification_time['event_start_datetime'];
                $archery_event_qualification_time->event_end_datetime =  $qualification_time['event_end_datetime'];
                $archery_event_qualification_time->save();
            }else{
                $archery_event_qualification_time->category_detail_id = $qualification_time['category_detail_id'];
                $archery_event_qualification_time->event_start_datetime =  $qualification_time['event_start_datetime'];
                $archery_event_qualification_time->event_end_datetime =  $qualification_time['event_end_datetime'];
                $archery_event_qualification_time->save();
            }
        }
        
        return $archery_event_qualification_time;
    }

    protected function validation($parameters)
    {
        return [
            "qualification_time" => "required|array|min:1",
        ];
    }
}