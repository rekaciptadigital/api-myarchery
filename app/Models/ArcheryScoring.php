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

    protected $elimination_scores_format_by_type = [
        "1" => [
            "shot" =>[
                ["score"=>["","","","","",""],"total"=>0,"status"=>"empty","point" => 0], // status = ["empty","win","draw","lose"]
                ["score"=>["","","","","",""],"total"=>0,"status"=>"empty","point" => 0],
                ["score"=>["","","","","",""],"total"=>0,"status"=>"empty","point" => 0],
                ["score"=>["","","","","",""],"total"=>0,"status"=>"empty","point" => 0],
                ["score"=>["","","","","",""],"total"=>0,"status"=>"empty","point" => 0]
            ],
            "extra_shot" => [
                    ["distance_from_x" => 0, "score" => "", "status" => "empty"],
                    ["distance_from_x" => 0, "score" => "", "status" => "empty"],
                    ["distance_from_x" => 0, "score" => "", "status" => "empty"],
                    ["distance_from_x" => 0, "score" => "", "status" => "empty"],
                    ["distance_from_x" => 0, "score" => "", "status" => "empty"]
                ],
            "win" => 0,
            "total" => 0,
            "eliminationt_score_type" => 1
        ],

        "2" => ["shot" =>[
                    ["score"=>["","","","","",""],"total"=>0],
                    ["score"=>["","","","","",""],"total"=>0],
                    ["score"=>["","","","","",""],"total"=>0],
                    ["score"=>["","","","","",""],"total"=>0],
                    ["score"=>["","","","","",""],"total"=>0]
                ],
        "extra_shot" => [
                ["distance_from_x" => 0, "score" => "", "status" => "empty"],
                ["distance_from_x" => 0, "score" => "", "status" => "empty"],
                ["distance_from_x" => 0, "score" => "", "status" => "empty"],
                ["distance_from_x" => 0, "score" => "", "status" => "empty"],
                ["distance_from_x" => 0, "score" => "", "status" => "empty"]
            ],
        "win" => 0,
        "total" => 0,
        "eliminationt_score_type" => 2]
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

    protected function makeEliminationScoringTypePointFormat(object $scoring){
        $scores = $this->elimination_scores_format_by_type[1];
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

    protected function calculateEliminationScoringTypePointFormat(array $scoring_1, array $scoring_2, $save_permanent){
        $scores = $this->elimination_scores_format_by_type[1];
        $total_point_1 = 0;
        $total_point_2 = 0;
        $total_score_1 = 0;
        $total_score_2 = 0;
        $win_1 = 0;
        $win_2 = 0;

        foreach ($scores["shot"] as $k => $shot) {
            $scoring_1_total_score_per_rambahan = 0;
            $scoring_2_total_score_per_rambahan = 0;
            $point_1_per_rambahan = 0;
            $point_2_per_rambahan = 0;
            foreach ($shot["score"] as $i => $s) {
                $s1 = $this->score_value[$scoring_1["scores"]["shot"][$k]["score"][$i]];
                $scoring_1_total_score_per_rambahan = $scoring_1_total_score_per_rambahan + $s1;
                $s2 = $this->score_value[$scoring_2["scores"]["shot"][$k]["score"][$i]];
                $scoring_2_total_score_per_rambahan = $scoring_2_total_score_per_rambahan + $s2;
            }

            $status_1 = "empty";
            $status_2 = "empty";

            $total_score_1 = $total_score_1+$scoring_1_total_score_per_rambahan;
            $total_score_2 = $total_score_2+$scoring_2_total_score_per_rambahan;
            $scoring_1["scores"]["shot"][$k]["total"] = $scoring_1_total_score_per_rambahan;
            $scoring_2["scores"]["shot"][$k]["total"] = $scoring_2_total_score_per_rambahan;

            if($scoring_1_total_score_per_rambahan != 0 || $scoring_2_total_score_per_rambahan != 0){
                if($scoring_1_total_score_per_rambahan > $scoring_2_total_score_per_rambahan){
                    $status_1 = "win";
                    $status_2 = "lose";
                    $point_1_per_rambahan = $point_1_per_rambahan+2;
                    $point_2_per_rambahan = $point_2_per_rambahan+0;
                }
                if($scoring_2_total_score_per_rambahan > $scoring_1_total_score_per_rambahan){
                    $status_1 = "lose";
                    $status_2 = "win";
                    $point_1_per_rambahan = $point_1_per_rambahan+0;
                    $point_2_per_rambahan = $point_2_per_rambahan+2;
                }
                if($scoring_1_total_score_per_rambahan == $scoring_2_total_score_per_rambahan){
                    $status_1 = "draw";
                    $status_2 = "draw";
                    $point_1_per_rambahan = $point_1_per_rambahan+1;
                    $point_2_per_rambahan = $point_2_per_rambahan+1;
                }
            }
                        
                $scoring_1["scores"]["shot"][$k]["status"] = $status_1;
                $scoring_2["scores"]["shot"][$k]["status"] = $status_2;
                $total_point_1 = $total_point_1+$point_1_per_rambahan;
                $total_point_2 = $total_point_2+$point_2_per_rambahan;
                $scoring_1["scores"]["shot"][$k]["point"] = $point_1_per_rambahan;
                $scoring_2["scores"]["shot"][$k]["point"] = $point_2_per_rambahan;
        }

        if(($total_point_1 !=0 || $total_point_2 !=0) && $total_point_1 < $total_point_2){
            $win_2 = 1;
        }
        if(($total_point_1 !=0 || $total_point_2 !=0) && $total_point_1 > $total_point_2){
            $win_1 = 1;
        }

        if($total_point_1 !=0 && $total_point_2 !=0 && $total_point_1 == $total_point_2){
            foreach ($scores["extra_shot"] as $es => $extra_shot) {
                $es_score_1 = $scoring_1["scores"]["extra_shot"][$es]["score"];
                $es_score_2 = $scoring_2["scores"]["extra_shot"][$es]["score"];
                $es_distance_1 = $scoring_1["scores"]["extra_shot"][$es]["distance_from_x"];
                $es_distance_2 = $scoring_2["scores"]["extra_shot"][$es]["distance_from_x"];
                $es_status_2 = "draw";
                $es_status_1 = "draw";
                if($es_score_1 == 0 && $es_score_2 == 0)
                    break;

                $total_score_1 = $total_score_1+$es_score_1;
                $total_score_2 = $total_score_2+$es_score_2;
                if($es_score_2 == $es_score_1){
                    if($es_distance_1 < $es_distance_2){
                        $es_status_2 = "lose";
                        $es_status_1 = "win";
                    }
                    if($es_distance_2 < $es_distance_1){
                        $es_status_2 = "win";
                        $es_status_1 = "lose";
                    }
                }
                if($es_score_2 > $es_score_1){
                    $es_status_2 = "win";
                    $es_status_1 = "lose";
                }
                if($es_score_1 > $es_score_2){
                    $es_status_2 = "lose";
                    $es_status_1 = "win";
                }
                $scoring_1["scores"]["extra_shot"][$es]["status"] = $es_status_1;
                $scoring_2["scores"]["extra_shot"][$es]["status"] = $es_status_2;
            }
        }

        if($save_permanent == 1){
            $scoring_1["scores"]["win"] = $win_1;
            $scoring_2["scores"]["win"] = $win_2;    
        }

        $scoring_1["scores"]["total"] = $total_score_1;
        $scoring_2["scores"]["total"] = $total_score_2;

        $scoring_1["scores"]["eliminationt_score_type"] = 1;
        $scoring_2["scores"]["eliminationt_score_type"] = 1;

        return [
            $scoring_1["member_id"] => $scoring_1,
            $scoring_2["member_id"] => $scoring_2,
        ];
    }

    protected function calculateEliminationScoringTypeTotalFormat(array $scoring_1, array $scoring_2, $save_permanent){
        $scores = $this->elimination_scores_format_by_type[2];
        $total_score_1 = 0;
        $total_score_2 = 0;
        $win_1 = 0;
        $win_2 = 0;

        foreach ($scores["shot"] as $k => $shot) {
            $scoring_1_total_score_per_rambahan = 0;
            $scoring_2_total_score_per_rambahan = 0;
            foreach ($shot["score"] as $i => $s) {
                $s1 = $this->score_value[$scoring_1["scores"]["shot"][$k]["score"][$i]];
                $scoring_1_total_score_per_rambahan = $scoring_1_total_score_per_rambahan + $s1;
                $s2 = $this->score_value[$scoring_2["scores"]["shot"][$k]["score"][$i]];
                $scoring_2_total_score_per_rambahan = $scoring_2_total_score_per_rambahan + $s2;
            }

            $status_1 = "empty";
            $status_2 = "empty";

            $total_score_1 = $total_score_1+$scoring_1_total_score_per_rambahan;
            $total_score_2 = $total_score_2+$scoring_2_total_score_per_rambahan;
            $scoring_1["scores"]["shot"][$k]["total"] = $scoring_1_total_score_per_rambahan;
            $scoring_2["scores"]["shot"][$k]["total"] = $scoring_2_total_score_per_rambahan;
        }

        if(($total_score_1 !=0 || $total_score_2 !=0) && $total_score_1 < $total_score_2){
            $win_2 = 1;
        }
        if(($total_score_1 !=0 || $total_score_2 !=0) && $total_score_1 > $total_score_2){
            $win_1 = 1;
        }

        if($total_score_1 !=0 && $total_score_2 !=0 && $total_score_1 == $total_score_2){
            foreach ($scores["extra_shot"] as $es => $extra_shot) {
                $es_score_1 = $scoring_1["scores"]["extra_shot"][$es]["score"];
                $es_score_2 = $scoring_2["scores"]["extra_shot"][$es]["score"];
                $es_distance_1 = $scoring_1["scores"]["extra_shot"][$es]["distance_from_x"];
                $es_distance_2 = $scoring_2["scores"]["extra_shot"][$es]["distance_from_x"];
                $es_status_2 = "draw";
                $es_status_1 = "draw";
                if($es_score_1 == 0 && $es_score_2 == 0)
                    break;
    
                $total_score_1 = $total_score_1+$es_score_1;
                $total_score_2 = $total_score_2+$es_score_2;
                if($es_score_2 == $es_score_1){
                    if($es_distance_1 < $es_distance_2){
                        $es_status_2 = "lose";
                        $es_status_1 = "win";
                    }
                    if($es_distance_2 < $es_distance_1){
                        $es_status_2 = "win";
                        $es_status_1 = "lose";
                    }
                }
                if($es_score_2 > $es_score_1){
                    $es_status_2 = "win";
                    $es_status_1 = "lose";
                }
                if($es_score_1 > $es_score_2){
                    $es_status_2 = "lose";
                    $es_status_1 = "win";
                }
                $scoring_1["scores"]["extra_shot"][$es]["status"] = $es_status_1;
                $scoring_2["scores"]["extra_shot"][$es]["status"] = $es_status_2;
            }
        }
        
        if($save_permanent == 1){
            $scoring_1["scores"]["win"] = $win_1;
            $scoring_2["scores"]["win"] = $win_2;    
        }

        $scoring_1["scores"]["total"] = $total_score_1;
        $scoring_2["scores"]["total"] = $total_score_2;

        $scoring_1["scores"]["eliminationt_score_type"] = 2;
        $scoring_2["scores"]["eliminationt_score_type"] = 2;

        return [
            $scoring_1["member_id"] => $scoring_1,
            $scoring_2["member_id"] => $scoring_2,
        ];
    }

    protected function makeEliminationScoringTypeTotalFormat(object $scoring){
        $scores = $this->elimination_scores_format_by_type[2];

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
        return (object)["total_tmp_string" => $this->getTotalTmpString($total_per_points, $total),"total_tmp" => $total_tmp,"total" => $total, "scors" => $scors];
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

    protected function getTotalTmpString(array $total_per_point,$total){
        $one = $total_per_point[1] < 10 ? "0".$total_per_point[1]: $total_per_point[1];
        $two = $total_per_point[2] < 10 ? "0".$total_per_point[2]: $total_per_point[2];
        $three = $total_per_point[3] < 10 ? "0".$total_per_point[3]: $total_per_point[3];
        $four = $total_per_point[4] < 10 ? "0".$total_per_point[4]: $total_per_point[4];
        $five = $total_per_point[5] < 10 ? "0".$total_per_point[5]: $total_per_point[5];
        $six = $total_per_point[6] < 10 ? "0".$total_per_point[6]: $total_per_point[6];
        $seven = $total_per_point[7] < 10 ? "0".$total_per_point[7]: $total_per_point[7];
        $eight = $total_per_point[8] < 10 ? "0".$total_per_point[8]: $total_per_point[8];
        $nine = $total_per_point[9] < 10 ? "0".$total_per_point[9]: $total_per_point[9];
        $ten = $total_per_point[10] < 10 ? "0".$total_per_point[10]: $total_per_point[10];
        $x = $total_per_point["x"] < 10 ? "0".$total_per_point["x"]: $total_per_point["x"];
        $x_plus_y = $x+$ten;
        $x_plus_y = $x_plus_y < 10 ? "0".$x_plus_y : $x_plus_y;
        $output = $total." "."".$x_plus_y."".$x."".$ten."".$nine."".$eight."".$seven."".$six."".$five."".$four."".$three."".$two."".$one;
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
