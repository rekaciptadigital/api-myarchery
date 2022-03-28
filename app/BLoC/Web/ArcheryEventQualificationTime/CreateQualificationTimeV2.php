<?php

namespace App\BLoC\Web\ArcheryEventQualificationTime;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventQualificationTime;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use App\Models\ArcheryEventParticipant;
use DAI\Utils\Exceptions\BLoCException;
use DateTime;
use Illuminate\Support\Carbon;

class CreateQualificationTimeV2 extends Transactional
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

            if ($event->admin_id != $admin->id) {
                throw new BLoCException("forbiden");
            }

            $date_time_event_start_register = strtotime($event->registration_start_datetime);
            $date_time_event_end_register = strtotime($event->registration_end_datetime);
            $today = strtotime("now");

            if (($today < $date_time_event_start_register) && ($today > $date_time_event_end_register)) {
                throw new BLoCException("hanya dapat diatur sebelum berlangsungnya event");
            }

            $date_time_event_start_datetime = strtotime($event->event_start_datetime);
            $date_time_event_end_datetime = strtotime($event->event_end_datetime);

            $qualification_time_event_start_datetime = strtotime($qualification_time['event_start_datetime']);
            $qualification_time_event_end_datetime = strtotime($qualification_time['event_end_datetime']);

            if (
                (($qualification_time_event_start_datetime >= $date_time_event_start_datetime) && ($qualification_time_event_start_datetime <= $date_time_event_end_datetime))
                &&
                (($qualification_time_event_end_datetime >= $date_time_event_start_datetime) && ($qualification_time_event_end_datetime <= $date_time_event_end_datetime))

            ) {
                if ($qualification_time_event_end_datetime < $qualification_time_event_start_datetime) {
                    throw new BLoCException("waktu mulai harus lebih kecil dari waktu selesai");
                }
            } else {
                throw new BLoCException("harus di set pada rentang tanggal event");
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

        return "ok";
    }

    protected function validation($parameters)
    {
        return [
            "qualification_time" => "required|array|min:1",
        ];
    }
}
