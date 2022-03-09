<?php

namespace App\BLoC\Web\ArcheryEventQualificationTime;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventQualificationTime;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use App\Models\ArcheryEventParticipant;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Carbon;

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
            $category_detail_id = $qualification_time['category_detail_id'];

            $category = ArcheryEventCategoryDetail::find($category_detail_id);
            if (!$category) {
                throw new BLoCException("kategori tidak ditemukan");
            }

            $event = ArcheryEvent::find($category->event_id);
            if (!$event) {
                throw new BLoCException("event tidak ditemukan");
            }

            $carbon_event_end_datetime = Carbon::parse($event->event_end_datetime);
            $new_format_event_end_datetime = Carbon::create($carbon_event_end_datetime->year, $carbon_event_end_datetime->month, $carbon_event_end_datetime->day, 0, 0, 0);

            if ($new_format_event_end_datetime < Carbon::today()) {
                throw new BLoCException('event telah selesai');
            }

            $archery_event_qualification_time = ArcheryEventQualificationTime::where('category_detail_id', $category_detail_id)->first();
            if (!$archery_event_qualification_time) {
                $archery_event_qualification_time = new ArcheryEventQualificationTime();
                $archery_event_qualification_time->category_detail_id = $qualification_time['category_detail_id'];
                $archery_event_qualification_time->event_start_datetime =  $qualification_time['event_start_datetime'];
                $archery_event_qualification_time->event_end_datetime =  $qualification_time['event_end_datetime'];
                $archery_event_qualification_time->save();
            } else {
                $count_participant = ArcheryEventParticipant::where('event_category_id', $qualification_time['category_detail_id'])->first();

                if (empty($count_participant)) {
                    $archery_event_qualification_time->category_detail_id = $qualification_time['category_detail_id'];
                    $archery_event_qualification_time->event_start_datetime =  $qualification_time['event_start_datetime'];
                    $archery_event_qualification_time->event_end_datetime =  $qualification_time['event_end_datetime'];
                    $archery_event_qualification_time->save();
                }
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
