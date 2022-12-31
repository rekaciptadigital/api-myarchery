<?php

namespace App\BLoC\Web\EndpointSupport;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use DAI\Utils\Abstracts\Retrieval;

class ResetBudrestQualification extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $participants = ArcheryEventParticipant::where("event_id", $parameters->get("event_id"))
            ->where("status", 1)
            ->get();

        foreach ($participants as $p) {
            $member = ArcheryEventParticipantMember::where("archery_event_participant_id", $p->id)->first();
            if ($member) {
                $schedule = ArcheryEventQualificationScheduleFullDay::where("participant_member_id", $member->id)->first();
                if ($schedule) {
                    $schedule->bud_rest_number = 0;
                    $schedule->target_face = "";
                    $schedule->save();
                }
            }
        }

        return ArcheryEventParticipant::join("archery_event_participant_members", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->join("archery_event_qualification_schedule_full_day", "archery_event_qualification_schedule_full_day.participant_member_id", "=", "archery_event_participant_members.id")
            ->where("archery_event_participants.event_id", $parameters->get("event_id"))->get();
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => "required|exists:archery_events,id",
        ];
    }
}
