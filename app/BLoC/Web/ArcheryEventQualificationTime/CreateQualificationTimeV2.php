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
        $event_id = $parameters->get("event_id");
        $qualification_times = $parameters->get('qualification_time', []);

        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException("forbiden");
        }

        $today = strtotime("now");
        $date_time_event_start_datetime = strtotime($event->event_start_datetime);
        $date_time_event_end_datetime = strtotime($event->event_end_datetime);

        // validasi hanya bisa set jadwal sebelum event mulai
        if ($today > $date_time_event_start_datetime) {
            throw new BLoCException("hanya dapat diatur sebelum event dimulai");
        }

        foreach ($qualification_times as $qualification_time) {
            $category_detail_id = $qualification_time['category_detail_id'];

            $category = ArcheryEventCategoryDetail::find($category_detail_id);
            if (!$category) {
                throw new BLoCException("kategori tidak ditemukan");
            }

            if ($category->is_show != 1) {
                throw new BLoCException("is_show harus 1");
            }

            if ($category->event_id != $event_id) {
                throw new BLoCException("category tidak valid");
            }

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
                $archery_event_qualification_time->category_detail_id = $category_detail_id;
                $archery_event_qualification_time->event_start_datetime =  $qualification_time['event_start_datetime'];
                $archery_event_qualification_time->event_end_datetime =  $qualification_time['event_end_datetime'];
                $archery_event_qualification_time->save();
            } else {
                $count_participant = ArcheryEventParticipant::where('event_category_id', $qualification_time['category_detail_id'])->first();

                if (empty($count_participant)) {
                    $archery_event_qualification_time->category_detail_id = $category_detail_id;
                    $archery_event_qualification_time->event_start_datetime =  $qualification_time['event_start_datetime'];
                    $archery_event_qualification_time->event_end_datetime =  $qualification_time['event_end_datetime'];
                    $archery_event_qualification_time->save();
                } else {
                    throw new BLoCException("tidak bisa ganti jadwal karena sudah ada peserta");
                }
            }

            $key_qualification_id = array_key_exists("qualification_time_id", $qualification_time);
            $kaey_deleted = array_key_exists("deleted", $qualification_time);
            if (($key_qualification_id && $kaey_deleted) && $kaey_deleted == 1) {
                $jadwal = ArcheryEventQualificationTime::find($qualification_time["qualification_time_id"]);
                if (!$jadwal) {
                    throw new BLoCException("qualification time tidak di temukan");
                }

                $jadwal->delete();
            }
        }

        return "success";
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required",
            "qualification_time" => "required|array|min:1",
        ];
    }
}
