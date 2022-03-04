<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ArcheryEventSerie;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcherySeriesCategory;
use App\Models\City;
use App\Models\User;
use App\Models\ArcheryScoring;
use App\Models\ArcherySeriesMasterPoint;

class ArcherySeriesUserPoint extends Model
{
    protected $table = 'archery_serie_user_point';
    protected $guarded = ['id'];

    protected function setPoint($member_id,$type,$pos){
        $member = ArcheryEventParticipantMember::find($member_id);
        if (!$member) return false;

        $participant = ArcheryEventParticipant::find($member->archery_event_participant_id);
        if(!$participant) return false;

        $user_id = $participant->user_id;
        $category_id = $participant->event_category_id;
        $category = ArcheryEventCategoryDetail::find($category_id);
        if (!$category) return false;

        $event_serie = ArcheryEventSerie::where("event_id", $category->event_id)->first();
        if (!$event_serie) return false;

        $archerySeriesCategory = ArcherySeriesCategory::where("age_category_id", $category->age_category_id)
            ->where("competition_category_id", $category->competition_category_id)
            ->where("distance_id", $category->distance_id)
            ->where("team_category_id", $category->team_category_id)
            ->where("serie_id", $event_serie->serie_id)
            ->first();
        if (!$archerySeriesCategory) return false;
        $t = 1;
        if ($type == "elimination") {
            $t = 2;
        }

        $point = ArcherySeriesMasterPoint::where("type", $t)->where("serie_id", $event_serie->serie_id)->where("start_pos", "<=", $pos)->where("end_pos", ">=", $pos)->first();
        if (!$point) return false;

        // get detail event
        $this->create([
            "event_serie_id" => $event_serie->id,
            "user_id" => $user_id,
            "event_category_id" => $archerySeriesCategory->id,
            "point" => $point->point,
            "status" => $member->is_series,
            "type" => $type,
            "position" => $pos,
            "member_id" => $member_id,
        ]);
    }

    protected function getUserSeriePointByCategory($category_serie_id)
    {
        $category_series = ArcherySeriesCategory::find($category_serie_id);
        $archery_user_point = ArcherySeriesUserPoint::where("event_category_id", $category_series->id)->where("status", 1)->get();
        $users = [];
        $output = [];
        foreach ($archery_user_point as $key => $value) {
            $member_score_details = isset($users[$value->user_id]) && isset($users[$value->user_id]["score_detail"]) ? $users[$value->user_id]["score_detail"] : ArcheryScoring::ArcheryScoringDetailPoint();
            $member_score_detail_qualification = isset($users[$value->user_id]) && isset($users[$value->user_id]["score_detail_qualification"]) ? $users[$value->user_id]["score_detail_qualification"] : ArcheryScoring::ArcheryScoringDetailPoint();
            if($value->type == "qualification"){
                $scores = ArcheryScoring::where("participant_member_id", $value->member_id)->where("type",1)->get();
                foreach ($scores as $s => $score) {
                    $score_details = json_decode($score->scoring_detail);
                    foreach ($score_details as $score_detail) {
                        foreach ($score_detail as $sd) {
                            $member_score_details[$sd->id] = $member_score_details[$sd->id] + 1;
                            $member_score_detail_qualification[$sd->id] = $member_score_detail_qualification[$sd->id] + 1;
                        }
                    }
                }
            }else{
                $scores = ArcheryScoring::where("participant_member_id", $value->member_id)->where("type",2)->get();
                foreach ($scores as $s => $score) {
                    $score_details = json_decode($score->scoring_detail);
                    foreach ($score_details->shot as $shot) {
                        foreach ($shot->score as $sps) {
                            $member_score_details[$sps] = $member_score_details[$sps] + 1;
                        }
                    }
                }
            }
            
            $users[$value->user_id]["score_detail"] = $member_score_details;
            $users[$value->user_id]["score_detail_qualification"] = $member_score_detail_qualification;
            $users[$value->user_id]["total_point"] = isset($users[$value->user_id]["total_point"]) ? $users[$value->user_id]["total_point"] + $value->point : $value->point;
        }

        foreach ($users as $u => $user) {
            $user_detail = User::select("id", "name", "avatar", "address_city_id")->where("id", $u)->first();
            $city = "";
            $total_score = 0;
            foreach ($user["score_detail_qualification"] as $x => $v) {
                if(in_array($x,[1,2,3,4,5,6,7,8,9,10,"x"])){
                    $score_value = $x == "x" ? 10 : $x;
                    $total_score = $total_score + ($score_value*$v);
                }
            }
            if (!empty($user_detail->address_city_id)) {
                $c = City::find($user_detail->address_city_id);
                $city = $c->name;
            }
            
            $user_profile = [
                "id" => $user_detail->id,
                "name" => $user_detail->name,
                "avatar" => $user_detail->avatar,
                "city" => $city,
            ];
            
            $output[] = [
                "tmp_score" => ArcheryScoring::getTotalTmp($user["score_detail_qualification"], $total_score,0.001),
                "total_point" => $user["total_point"],
                "user" => $user_profile,
            ];
        }

        usort($output, function ($a, $b) {
            if($a["total_point"] == $b["total_point"]){
                return $b["tmp_score"] > $a["tmp_score"] ? 1 : -1;
            }
            if($a["total_point"] < $b["total_point"]){
                return 1;
            }
            return -1;
        });

        return $output;
    }
}
