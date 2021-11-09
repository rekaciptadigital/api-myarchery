<?php

namespace App\BLoC\Web\EventElimination;

use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryEventEliminationSchedule;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventCategoryDetail;
use Illuminate\Support\Facades\DB;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;
use App\Models\ArcheryScoring;
use App\Models\ArcheryEventElimination;

class GetEventEliminationTemplate extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        
        $event_id = $parameters->get('event_id');
        $match_type = $parameters->get('match_type');
        $elimination_member_count = $parameters->get('elimination_member_count');
        $event_category_id = $parameters->get("event_category_id");
        $gender = $parameters->get('gender');

        $elimination = ArcheryEventElimination::where("event_category_id",$event_category_id)->where("gender",$gender)->first();
        $elimination_id = 0;
        if($elimination){
            $match_type = $elimination->elimination_type;
            $elimination_member_count = $elimination->count_participant;
            $gender = $elimination->gender;
            $elimination_id = $elimination->id;
        }

        $category = ArcheryEventCategoryDetail::find($event_category_id);
        $team_category_id = $category["team_category_id"];
        $competition_category_id = $category["competition_category_id"];
        $distance_id = $category["distance_id"];
        $age_category_id = $category["age_category_id"];
        $score_type = 1; // 1 for type qualification
        

        $fix_members = ArcheryEventEliminationMatch::select(
                            "archery_event_elimination_members.position_qualification",
                            "archery_event_participant_members.name",
                            "archery_event_participant_members.id AS member_id",
                            "archery_event_participant_members.club",
                            "archery_event_participant_members.gender",
                            "archery_event_elimination_matches.id",
                            "archery_event_elimination_matches.round",
                            "archery_event_elimination_matches.match",
                            "archery_event_elimination_matches.win",
                            "archery_event_elimination_schedules.date",
                            "archery_event_elimination_schedules.start_time",
                            "archery_event_elimination_schedules.end_time"
                        )
                        ->leftJoin("archery_event_elimination_members","archery_event_elimination_matches.elimination_member_id","=","archery_event_elimination_members.id")
                        ->leftJoin("archery_event_participant_members","archery_event_elimination_members.member_id","=","archery_event_participant_members.id")
                        ->leftJoin("archery_event_elimination_schedules","archery_event_elimination_matches.elimination_schedule_id","=","archery_event_elimination_schedules.id")
                        ->where("archery_event_elimination_matches.gender",$gender)->where("archery_event_elimination_matches.event_elimination_id",$elimination_id)->get();
        $qualification_rank = [];
        $updated = true;
        if(count($fix_members) > 0){
            $members = [];
            foreach ($fix_members as $key => $value) {
                $members[$value->round][$value->match]["date"] = $value->date." ".$value->start_time." - ".$value->end_time; 
                if($value->name != null){
                    $members[$value->round][$value->match]["teams"][] = array(
                        "id" => $value->member_id,
                        "name" => $value->name,
                        "gender" => $value->gender,
                        "club" => $value->club,
                        "potition" => $value->position_qualification,
                        "win" => $value->win,
                        "status" => $value->win == 1 ? "win" : "wait"
                    );    
                }
                else{
                    $members[$value->round][$value->match]["teams"][] = ["status"=>"bye"];
                }
            }

            $fix_members = $members;
            $updated = false;
        }else{
            $qualification_rank = ArcheryScoring::getScoringRank($distance_id,$team_category_id,$competition_category_id,$age_category_id,$gender,$score_type,$event_id);
        }
        $qualification_rank = ArcheryScoring::getScoringRank($distance_id,$team_category_id,$competition_category_id,$age_category_id,$gender,$score_type,$event_id);
        // $template["rounds"] = ArcheryEventEliminationSchedule::makeTemplate2($qualification_rank, $elimination_member_count, $match_type, $event_category_id, $gender, $fix_members);
        $template["rounds"] = ArcheryEventEliminationSchedule::makeTemplate($qualification_rank, 16,[] );
        $template["updated"] = $updated;
        return $template;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
