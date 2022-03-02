<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ArcheryClub;
use App\Models\ArcheryQualificationSchedules;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventS;
use DAI\Utils\Abstracts\Retrieval;

class ArcheryEventParticipantMember extends Model
{
    protected $guarded = ['id'];
    protected function memberDetail($participant_member_id){
        $member = $this->find($participant_member_id);
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
        
        $participant->category_label = $category_label;
        $participant->member = $member;
        $club = ArcheryClub::find($participant->club_id);
        $participant->club = $club ? $club->name: "";
        return $participant;
    }
}
