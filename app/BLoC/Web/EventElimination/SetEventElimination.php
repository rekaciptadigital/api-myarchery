<?php

namespace App\BLoC\Web\EventElimination;

use App\Models\ArcheryScoring;
use App\Models\ArcheryQualificationSchedules;
use App\Models\ArcheryEventEliminationSchedule;
use DAI\Utils\Abstracts\Transactional;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Exceptions\BLoCException;
use App\Models\ArcheryEventEliminationMember;
use App\Models\ArcheryEventEliminationMatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SetEventElimination extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $check = ArcheryEventEliminationMatch::where("gender",$parameters->gender)->where("event_category_id",$parameters->event_category_id)->first();
        if($check)
            throw new BLoCException("match sudah di setting");
    
        $category = ArcheryEventCategoryDetail::find($parameters->event_category_id);
        $team_category_id = $category->team_category_id;
        $competition_category_id = $category->competition_category_id;
        $distance_id = $category->distance_id;
        $age_category_id = $category->age_category_id;
        $gender = $parameters->gender;
        $score_type = 1; // 1 for type qualification
        $event_id = $category->event_id;
        $match_type = $parameters->match_type;
        $elimination_member_count = $parameters->elimination_member_count;
        $qualification_rank = ArcheryScoring::getScoringRank($distance_id,$team_category_id,$competition_category_id,$age_category_id,$gender,$score_type,$event_id);
        $template = ArcheryEventEliminationSchedule::makeTemplate($qualification_rank, $elimination_member_count, $match_type, $parameters->event_category_id, $gender,[]);
        foreach ($template as $key => $value) {
            foreach ($value["seeds"] as $k => $v) {
                foreach ($v["teams"] as $i => $team){
                    $elimination_member_id = 0;
                    $member_id = isset($team->id) ? $team->id : 0;
                    $thread = $k;
                    $position_qualification = isset($team->postition) ? $team->postition : 0;
                    if($member_id != 0){
                        $em = ArcheryEventEliminationMember::where("member_id",$member_id)->first();
                        if($em){
                            $elimination_member = $em;
                        }else{
                            $elimination_member = new ArcheryEventEliminationMember;
                            $elimination_member->thread = $thread;
                            $elimination_member->member_id = $member_id;
                            $elimination_member->position_qualification = $position_qualification;
                            $elimination_member->save();
                        }
                        $elimination_member_id = $elimination_member->id;
                    }
                    $match = new ArcheryEventEliminationMatch;
                    $match->event_category_id = $parameters->event_category_id;
                    $match->elimination_member_id = $elimination_member_id;
                    $match->elimination_schedule_id = 0;
                    $match->round = $key+1;
                    $match->match = $k+1;
                    $match->gender = $parameters->gender;
                    $match->save();
                }
            }
        }
        return $template;
    }

    protected function validation($parameters)
    {
        return [
            'event_category_id' => 'required|exists:archery_event_category_details,id',
        ];
    }
}
