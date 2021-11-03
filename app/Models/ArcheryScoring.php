<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ArcheryEventParticipantMember;

class ArcheryScoring extends Model
{
    protected $score_value = [
        "" => 0,
        "1" => 1,
        "2" => 2,
        "3" => 3,
        "4" => 4,
        "5" => 5,
        "6" => 6,
        "7" => 7,
        "8" => 8,
        "9" => 9,
        "10" => 10,
        "x" => 10,
        "m" => 0,
    ];

    protected $score_type = array(
        array("id" => 1, "value" => "qualification"),
        array("id" => 2, "value" => "eliminasi")
    );

    protected function makeScoringFormat(object $scoring){
        $scores = [];
        if(empty((array)$scoring)){
            $scores = [
                "1" => ["","","","","",""],
                "2" => ["","","","","",""],
                "3" => ["","","","","",""],
                "4" => ["","","","","",""],
                "5" => ["","","","","",""],
                "6" => ["","","","","",""],
            ];
            return $scores;
        }
        foreach ($scoring as $key => $value) {
            $score = [];
            foreach ($value as $k => $v) {
                $score[] = (string)$v->id;
            }
            $scores[$key] = $score;
        }
        return $scores;
    }

    protected function makeEliminationScoringFormat(object $scoring){
        $scores = [
            "shot" =>[["","","","","",""],
                        ["","","","","",""],
                        ["","","","","",""],
                        ["","","","","",""],
                        ["","","","","",""]],
            "extra_shot" => [
                    ["distance_from_x" => "", "score" => ""],
                    ["distance_from_x" => "", "score" => ""],
                    ["distance_from_x" => "", "score" => ""],
                    ["distance_from_x" => "", "score" => ""],
                    ["distance_from_x" => "", "score" => ""]
                ],
        ];

        if(!empty((array)$scoring)){
            foreach ($scoring as $key => $value) {
                if($value->shot){
                    $score = [];
                    foreach ($value as $k => $v) {
                        $score[] = (string)$v->id;
                    }
                    $scores[$key] = $score;
                }
            }
        }
        return $scores;
    }

    protected function makeScoring(array $scoring){
        $total_per_points = [
            "" => 0,
            "1" => 0,
            "2" => 0,
            "3" => 0,
            "4" => 0,
            "5" => 0,
            "6" => 0,
            "7" => 0,
            "8" => 0,
            "9" => 0,
            "10" => 0,
            "x" => 0,
            "m" => 0,
        ];

        $scors = []; // data rambahan / keseluruhan arrow
        $total = 0;
        foreach ($scoring as $key => $value) {
            $arrows = [];
            if(!empty($value)){
                foreach ($value as $k => $arrow) {
                    $a = isset($this->score_value[$arrow]) ? $this->score_value[$arrow] : 0; 
                    $total = $total + $a;
                    $total_per_points[$arrow] = $total_per_points[$arrow] + 1;
                    $arrows[] = [ "id" => $arrow, "value" => $a];
                }
                $scors[$key] = $arrows;    
            }
        }
       
        $total_tmp = $this->getTotalTmp($total_per_points, $total);
        return (object)["total_tmp" => $total_tmp,"total" => $total, "scors" => $scors];
    }

    protected function generateScoreBySession(int $participant_member_id, int $type, array $filter_session = [1,2]){
        $total_per_points = [
                "" => 0,
                "1" => 0,
                "2" => 0,
                "3" => 0,
                "4" => 0,
                "5" => 0,
                "6" => 0,
                "7" => 0,
                "8" => 0,
                "9" => 0,
                "10" => 0,
                "x" => 0,
                "m" => 0,
        ];
        $member_scors = $this->where("participant_member_id",$participant_member_id)
                                        ->whereIn("scoring_session",$filter_session)
                                        ->where("type",$type)
                                        ->get();
        $sessions = [];
        foreach ($filter_session as $s) {
            $sessions[$s] = array(
                "score" => [],
                "total_per_point" => $total_per_points,
                "total" => 0,
                "total_tmp" => 0,
                "session" => $s
            );
        }
        $total = 0;
        $total_tmp = 0;
        foreach ($member_scors as $k => $score) {
                $score_detail = json_decode($score->scoring_detail);
                $score_rambahan = []; 
                $total_per_session = 0;
                foreach ($score_detail as $ks => $sd) {
                    $get_score = [];
                    foreach ($sd as $rambahan => $arrows) {
                        $get_score[] = $arrows->id;
                        $total = $total + $arrows->value;
                        $total_per_session = $total_per_session + $arrows->value;
                        $total_per_points[$arrows->id] = $total_per_points[$arrows->id] + 1;
                        $sessions[$score->scoring_session]["total_per_point"][$arrows->id] = $sessions[$score->scoring_session]["total_per_point"][$arrows->id] + 1;
                    }
                    $score_rambahan[$ks] = $get_score; 
                }
                $sessions[$score->scoring_session]["total_tmp"] = $this->getTotalTmp($sessions[$score->scoring_session]["total_per_point"], $total_per_session); 
                $sessions[$score->scoring_session]["score"] = $score_rambahan; 
                $sessions[$score->scoring_session]["total"] = $total_per_session; 
                $sessions[$score->scoring_session]["scoring_id"] = $score->id; 
        }
        $output = [
            "sessions" => $sessions,
            "total" => $total,
            "total_x" => $total_per_points["x"],
            "total_x_plus_ten" => $total_per_points["x"] + $total_per_points["10"],
            "total_tmp" => $this->getTotalTmp($total_per_points, $total),
        ];
        return $output;
    }

    protected function getTotalTmp(array $total_per_point,$total){
        $one = $total_per_point[1];
        $two = $total_per_point[2];
        $three = $total_per_point[3];
        $four = $total_per_point[4];
        $five = $total_per_point[5];
        $six = $total_per_point[6];
        $seven = $total_per_point[7];
        $eight = $total_per_point[8];
        $nine = $total_per_point[9];
        $ten = $total_per_point[10];
        $x = $total_per_point["x"];
        $x_plus_y = $x+$ten;
        $output = $total+(($x_plus_y+(($x+(($ten+(($nine+(($eight+(($seven+(($six+(($five+(($four+(($three+(($two+($one*0.01))*0.01))*0.01))*0.01))*0.01))*0.01))*0.01))*0.01))*0.01))*0.01))*0.01))*0.01);
        return $output;
    }

    // TODO gunakan nanti di GetParticipantScore
    protected function getScoringRank($distance_id,$team_category_id,$competition_category_id,$age_category_id,$gender,$score_type,$event_id){
        $archery_event_participant = ArcheryEventParticipantMember::select(
            "archery_event_participant_members.id",
            "archery_event_participant_members.name",
            "archery_event_participant_members.gender",
            "archery_event_participants.club"
        )->
        join("archery_event_participants","archery_event_participant_members.archery_event_participant_id","=","archery_event_participants.id")->
        join("transaction_logs","archery_event_participants.transaction_log_id","=","transaction_logs.id")->
        where('transaction_logs.status', 1)->
        where('archery_event_participants.event_id', $event_id);
        if (!is_null($team_category_id)) {
            $archery_event_participant->where('archery_event_participants.team_category_id', $team_category_id);
        }
        if (!is_null($distance_id)) {
            $archery_event_participant->where('archery_event_participants.distance_id', $distance_id);
        }
        if (!is_null($gender) && !empty($gender)) {
            $archery_event_participant->where('archery_event_participant_members.gender', $gender);
        }
        if (!is_null($competition_category_id)) {
            $archery_event_participant->where('archery_event_participants.competition_category_id', $competition_category_id);
        }
        if (!is_null($age_category_id)) {
            $archery_event_participant->where('archery_event_participants.age_category_id', $age_category_id);
        }

        $participants = $archery_event_participant->get();

        $archery_event_score = [];
        foreach ($participants as $key => $value) {
        $score = $this->generateScoreBySession($value->id,$score_type);
        $score["member"] = $value;
        $archery_event_score[] = $score;
        }

        usort($archery_event_score, function($a, $b) {return $b["total_tmp"] > $a["total_tmp"] ? 1 : -1;});

        return $archery_event_score;
    }
}
