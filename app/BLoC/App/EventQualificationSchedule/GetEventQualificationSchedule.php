<?php

namespace App\BLoC\App\EventQualificationSchedule;

use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryQualificationSchedules;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Helpers\BLoC;
use DAI\Utils\Exceptions\BLoCException;

class GetEventQualificationSchedule extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $member = ArcheryEventParticipantMember::find($parameters->participant_member_id);
        if(!$member)throw new BLoCException("member not found");
        
        $participant = ArcheryEventParticipant::find($member->archery_event_participant_id);
        $participant["category_label"] = $participant->team_category_id."-".$participant->age_category_id."-".$participant->competition_category_id."-".$participant->distance_id."m";
        $participant["member"] = $member;
        $schedule = ArcheryQualificationSchedules::list($participant->event_id, $parameters->participant_member_id);
        $output = array(
            "schedules" => $schedule["list"],
            "my_schedule" => $schedule["my_schedule"],
            "event" => $schedule["event"],
            "participant" => $participant,
            "disable_date" => $schedule["disable_date"]
        );
        return $output;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
