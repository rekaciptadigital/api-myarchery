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
        $member = ArcheryEventParticipantMember::find($parameters->member_id);
        $participant = ArcheryEventParticipant::find($member->archery_event_participant_id);
        $archery_event = ArcheryEvent::find($participant->event_id);
        $flat_categorie = $archery_event->flatCategories;
        $category_label = $participant->team_category_id."-".$participant->age_category_id."-".$participant->competition_category_id."-".$participant->distance_id."m";
        foreach ($flat_categorie as $key => $value) {
            if($value->age_category_id == $participant->age_category_id
            && $value->competition_category_id == $participant->competition_category_id
            && $value->team_category_id == $participant->team_category_id
            && $value->distance_id == $participant->distance_id
            ){
                $category_label = $value->archery_event_category_label;
            }
        }
        $schedule = ArcheryQualificationSchedules::memberDetail($parameters->member_id);

        $participant->schedule = $schedule;
        $participant->category_label = $category_label;
        $participant->member = $member;
        return $participant;
    }

    protected function validation($parameters)
    {
        return [
            'member_id' => 'required|exists:archery_event_participant_members,id',
        ];
    }
}
