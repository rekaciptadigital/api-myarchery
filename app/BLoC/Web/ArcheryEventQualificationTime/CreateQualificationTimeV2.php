<?php

namespace App\BLoC\Web\ArcheryEventQualificationTime;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventQualificationScheduleFullDay;
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

        if ($event->admin_id != $admin->id) {
            throw new BLoCException("forbiden");
        }

        $today = time();
        // ubah string mulai dan string selesai event menjadi objek timestamp
        $date_time_event_start_timestamp = strtotime($event->event_start_datetime);
        $date_time_event_end_timestamp = strtotime($event->event_end_datetime);

        // validasi hanya bisa set jadwal sebelum event mulai
        if ($today > $date_time_event_start_timestamp) {
            // throw new BLoCException("hanya dapat diatur sebelum event dimulai");
        }

        foreach ($qualification_times as $qt) {
            if (!isset($qt['category_detail_id'])) {
                continue;
            }
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
            $key_deleted = array_key_exists("deleted", $qt);

            if ($key_qualification_id && $key_deleted && $key_deleted == 1) {
                $check_is_exist = ArcheryEventQualificationTime::find($qt["qualification_time_id"]);
                if (!$check_is_exist) {
                    throw new BLoCException("jadwal tidak ditemukan untuk id " . $qt["qualification_time_id"] . " tidak ditemukan");
                }

                // hapus user yang telah terdaftar di jadwal tersebut
                $jadwal_member = ArcheryEventQualificationScheduleFullDay::where("qalification_time_id", $check_is_exist->id)->get();
                foreach ($jadwal_member as $jadwal) {
                    $jadwal->delete();
                }

                // hapus jadwal
                $check_is_exist->delete();
                continue;
            }


            // ubah string datetime yang diinputkan users menjadi format timestamp
            $qualification_time_event_start_timestamp = strtotime($qt['event_start_datetime']);
            $qualification_time_event_end_timestamp = strtotime($qt['event_end_datetime']);
            if (
                // cek apakah tanggal mulai dan tanggal selesai berada di dalam rentang tanggal pertandingan event
                (
                    ($qualification_time_event_start_timestamp >= $date_time_event_start_timestamp)
                    &&
                    ($qualification_time_event_start_timestamp <= $date_time_event_end_timestamp))
                &&
                (
                    ($qualification_time_event_end_timestamp >= $date_time_event_start_timestamp)
                    && ($qualification_time_event_end_timestamp <= $date_time_event_end_timestamp)
                )

            ) {
                if ($qualification_time_event_end_timestamp < $qualification_time_event_start_timestamp) {
                    throw new BLoCException("waktu mulai harus lebih kecil dari waktu selesai");
                }
            } else {
                throw new BLoCException("harus di set pada rentang tanggal event");
            }


            if ($key_qualification_id) {
                $check_is_exist = ArcheryEventQualificationTime::find($qt["qualification_time_id"]);
                if (!$check_is_exist) {
                    throw new BLoCException("jadwal tidak ditemukan untuk id " . $qt["qualification_time_id"] . " tidak ditemukan");
                }

                // update
                $check_is_exist->category_detail_id = $category_detail_id;
                $check_is_exist->event_start_datetime =  $qt['event_start_datetime'];
                $check_is_exist->event_end_datetime =  $qt['event_end_datetime'];
                $check_is_exist->save();
            } else {
                // create
                $new_archery_event_qualification_time = new ArcheryEventQualificationTime();
                $new_archery_event_qualification_time->category_detail_id = $category_detail_id;
                $new_archery_event_qualification_time->event_start_datetime =  $qt['event_start_datetime'];
                $new_archery_event_qualification_time->event_end_datetime =  $qt['event_end_datetime'];
                $new_archery_event_qualification_time->save();
            }
        }

        return "success";
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|exists:archery_events,id",
            "qualification_time" => "required|array|min:1",
        ];
    }
}
