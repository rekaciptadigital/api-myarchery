<?php

namespace App\BLoC\Web\EventQualificationScheduleByEo;

use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryQualificationSchedules;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;

class GetEventMemberQualificationScheduleByEo extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event = ArcheryEvent::find($parameters->event_id);
        $admin = Auth::user();

        $schedule_member = ArcheryQualificationSchedules::where("date",$parameters->date)->where("qualification_detail_id",$parameters->session_id)->orderBy("id","DESC")->get();
        $members = [];
        foreach ($schedule_member as $key => $value) {
            $member = ArcheryEventParticipantMember::find($value->participant_member_id);
            $participant = ArcheryEventParticipant::find($member->archery_event_participant_id);
            $participant->member = $member;
            $participant->schedule_id = $value->id;
            $participant->is_scoring = $value->is_scoring;
            $category_label = $participant->team_category_id."-".$participant->age_category_id."-".$participant->competition_category_id."-".$participant->distance_id."m";
            foreach ($event->flatCategories as $key => $v) {
                if($v->age_category_id == $participant->age_category_id
                && $v->competition_category_id == $participant->competition_category_id
                && $v->team_category_id == $participant->team_category_id
                && $v->distance_id == $participant->distance_id
                ){
                    $category_label = $v->archery_event_category_label;
                }
            }
            
            $participant->category_label = $category_label;
            $members [] = $participant;
        }

        return $members;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
