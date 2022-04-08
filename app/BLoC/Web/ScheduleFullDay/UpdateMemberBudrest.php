<?php

namespace App\BLoC\Web\ScheduleFullDay;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\BudRest;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetScheduleFullDay extends Retrieval
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
        $admin = Auth::user();

        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException('you are not owner this event');
        }

        $schedule_full_day = ArcheryEventQualificationScheduleFullDay::select("archery_event_qualification_schedule_full_day.*", "archery_event_participants.club_id")
            ->join("archery_event_participant_members", "archery_event_participant_members.id", "=", "archery_event_qualification_schedule_full_day.participant_member_idss")
            ->join("archery_event_participants", "archery_event_participants.id", "=", "archery_event_participant_members.archery_event_participant_id")
            ->where("archery_event_qualification_schedule_full_day.id", $schedule_full_day_id)->first();

        if (!$schedule_full_day) {
            throw new BLoCException("jadwal peserta tidak ditemukan");
        }

        $brn = preg_split('/(?<=[0-9])(?=[a-z]+)/i', $bud_rest_number);
        $bud_rest = $brn[0];
        $target_face = $brn[1];

        $schedule_full_day_2 = ArcheryEventQualificationScheduleFullDay::where("bud_rest_number", $bud_rest)
            ->where("target_face", $target_face)->firs();

        if ($schedule_full_day_2) {
            $schedule_full_day_2->bud_rest_number = "";
            $schedule_full_day_2->target_face = "";
            $schedule_full_day_2->save();
        }
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|integer",
            "schedule_id" => "required|integer"
        ];
    }
}
