<?php

namespace App\BLoC\Web\ArcheryEventParticipant;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEvent;
use App\Models\ArcheryQualificationSchedules;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventS;
use DAI\Utils\Abstracts\Retrieval;

class GetArcheryEventParticipantMemberProfile extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $participant = ArcheryEventParticipantMember::memberDetail($parameters->member_id);
        $schedule = ArcheryQualificationSchedules::memberScheduleDetail($parameters->member_id);

        $participant->schedule = $schedule;
        return $participant;
    }

    protected function validation($parameters)
    {
        return [
            'member_id' => 'required|exists:archery_event_participant_members,id',
        ];
    }
}
