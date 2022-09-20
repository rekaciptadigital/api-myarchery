<?php

namespace App\BLoC\Web\ScheduleFullDay;

use App\Models\AdminRole;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class UpdateMemberBudrest extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        // param
        $event_id = $parameters->get("event_id");
        $schedule_full_day_id = $parameters->get("schedule_id");
        $bud_rest_number = $parameters->get("bud_rest_number");
        $category_id = $parameters->get("category_id");
        $admin = Auth::user();

        // cek event
        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        // cek pemilik event
        if ($event->admin_id != $admin->id) {
            $role = AdminRole::where("admin_id", $admin->id)->where("event_id", $event->id)->first();
            if (!$role || $role->role_id != 6) {
                throw new BLoCException("you are not owner this event");
            }
        }

        // dapatkan jadwal peserta
        $schedule_full_day1 = ArcheryEventQualificationScheduleFullDay::select("archery_event_qualification_schedule_full_day.*", "archery_event_participants.club_id")
            ->join("archery_event_qualification_time", "archery_event_qualification_time.id", "=", "archery_event_qualification_schedule_full_day.qalification_time_id")
            ->join("archery_event_category_details", "archery_event_category_details.id", "=", "archery_event_qualification_time.category_detail_id")
            ->join("archery_event_participant_members", "archery_event_participant_members.id", "=", "archery_event_qualification_schedule_full_day.participant_member_id")
            ->join("archery_event_participants", "archery_event_participants.id", "=", "archery_event_participant_members.archery_event_participant_id")
            ->where("archery_event_qualification_schedule_full_day.id", $schedule_full_day_id)
            ->where("archery_event_qualification_time.category_detail_id", $category_id)
            ->where("archery_event_category_details.event_id", $event_id)
            ->first();

        if (!$schedule_full_day1) {
            // throw new BLoCException("jadwal peserta tidak ditemukan");
        }

        $bud_rest = 0;
        $target_face = "";

        // split budrest number dan target face
        $brn = preg_split('/(?<=[0-9])(?=[a-z]+)/i', $bud_rest_number);
        if (count($brn) == 1) {
            if (ctype_alpha($brn[0])) {
                throw new BLoCException("bantalan harus mengandung angka");
            }
            $bud_rest = $brn[0];
        } elseif (count($brn) == 2) {
            $bud_rest = $brn[0];
            $target_face = $brn[1];
        } else {
            throw new BLoCException("input invalid");
        }

        // cek apakah terdapat peserta di budrest tujuan
        $schedule_full_day_2 = ArcheryEventQualificationScheduleFullDay::select("archery_event_qualification_schedule_full_day.*")
            ->join("archery_event_qualification_time", "archery_event_qualification_time.id", "=", "archery_event_qualification_schedule_full_day.qalification_time_id")
            ->join("archery_event_category_details", "archery_event_category_details.id", "=", "archery_event_qualification_time.category_detail_id")
            ->where("archery_event_category_details.event_id", $event_id)
            ->where("bud_rest_number", $bud_rest)
            ->where("target_face", $target_face)
            ->where("archery_event_qualification_time.category_detail_id", $category_id)
            ->first();

        if ($schedule_full_day_2) {
            // cek apakah id schedule_full_day_1 dan 2 sama 
            if ($schedule_full_day1->id === $schedule_full_day_2->id) {
                throw new BLoCException("tidak dapat mengubah ke nomor bantalan yang sama");
            }
            // set ulang data member budrest
            $schedule_full_day_2->update([
                "bud_rest_number" => 0,
                "target_face" => "",
            ]);
        }

        $schedule_full_day1->update([
            "bud_rest_number" => $bud_rest,
            "target_face" => $target_face,
        ]);

        return $schedule_full_day1;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|integer",
            "schedule_id" => "required|integer",
            "bud_rest_number" => "required",
            "category_id" => "required"
        ];
    }
}
