<?php

namespace App\BLoC\Web\ArcheryEventQualificationTime;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventQualificationTime;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;

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

        $today = time();
        // ubah string mulai dan string selesai event menjadi objek timestamp
        $date_time_event_start_datetime = strtotime($event->event_start_datetime);
        $date_time_event_end_datetime = strtotime($event->event_end_datetime);

        // validasi hanya bisa set jadwal sebelum event mulai
        if ($today > $date_time_event_start_datetime) {
            throw new BLoCException("hanya dapat diatur sebelum event dimulai");
        }

        foreach ($qualification_times as $qt) {
            $category_detail_id = $qt['category_detail_id'];

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

            $key_qualification_id = array_key_exists("qualification_time_id", $qt);
            $kaey_deleted = array_key_exists("deleted", $qt);

            $archery_event_qualification_time = ArcheryEventQualificationTime::where("category_detail_id", $category_detail_id)
                ->first();

            if ($archery_event_qualification_time) {
                if ($kaey_deleted && $kaey_deleted == 1) {
                    $archery_event_qualification_time->delete();
                    continue;
                }
            } else {
                // ubah string datetime yang diinputkan users menjadi format datetime
                $qualification_time_event_start_datetime = strtotime($qt['event_start_datetime']);
                $qualification_time_event_end_datetime = strtotime($qt['event_end_datetime']);
                if (
                    // cek apakah tanggal mulai dan tanggal selesai berada di dalam rentang tanggal pertandingan event
                    (
                        ($qualification_time_event_start_datetime >= $date_time_event_start_datetime)
                        &&
                        ($qualification_time_event_start_datetime <= $date_time_event_end_datetime))
                    &&
                    (
                        ($qualification_time_event_end_datetime >= $date_time_event_start_datetime)
                        && ($qualification_time_event_end_datetime <= $date_time_event_end_datetime)
                    )

                ) {
                    if ($qualification_time_event_end_datetime < $qualification_time_event_start_datetime) {
                        throw new BLoCException("waktu mulai harus lebih kecil dari waktu selesai");
                    }
                } else {
                    throw new BLoCException("harus di set pada rentang tanggal event");
                }
                
                $archery_event_qualification_time = new ArcheryEventQualificationTime();
            }

            $archery_event_qualification_time->category_detail_id = $category_detail_id;
            $archery_event_qualification_time->event_start_datetime =  $qt['event_start_datetime'];
            $archery_event_qualification_time->event_end_datetime =  $qt['event_end_datetime'];
            $archery_event_qualification_time->save();
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
