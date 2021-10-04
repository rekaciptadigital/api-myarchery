<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryScoring extends Model
{
    protected $score_value = [
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

    protected function makeScoring(array $scoring){
        $scors = []; // data rambahan / keseluruhan arrow
        $total = 0;
        foreach ($scoring as $key => $value) {
            $arrows = [];
            if(!empty($value)){
                foreach ($value as $k => $arrow) {
                    $a = isset($this->score_value[$arrow]) ? $this->score_value[$arrow] : 0; 
                    $total = $total + $a;
                    $arrows[] = [ "id" => $arrow, "value" => $a];
                }
                $scors[$key] = $arrows;    
            }
        }
       
        return (object)["total" => $total, "scors" => $scors];
    }

    protected function generateScoreBySession(int $participant_member_id, int $type, array $filter_session = [1,2]){
        $total_per_points = [
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
                "total_tmp" => 0
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
}
