<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryScoring;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryQualificationSchedules;
use App\Models\ArcheryMasterTeamCategory;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipant;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\DB;
use DAI\Utils\Abstracts\Retrieval;

class GetParticipantScoreQualification extends Retrieval
{
    var $total_per_points = [
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

    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $team_category_id = $parameters->get('team_category_id');
        $competition_category_id = $parameters->get('competition_category_id');
        $age_category_id = $parameters->get('age_category_id');
        $gender = $parameters->get('gender');
        $score_type = 1;
        $event_id = $parameters->get('event_id');
        $distance_id = $parameters->get('distance_id');
        $event_category_id = $parameters->get('event_category_id');
        $category_detail = ArcheryEventCategoryDetail::find($event_category_id);
        $team_category = ArcheryMasterTeamCategory::find($category_detail->team_category_id);

        $session = [];
        for ($i=0; $i < $category_detail->session_in_qualification; $i++) { 
            $session[] = $i+1;
        }

        if(strtolower($team_category->type) == "team"){
            if($team_category->id == "mix_team"){
                return $this->mixTeamBestOfThree($category_detail,$team_category,$session);
            }else{
                return $this->teamBestOfThree($category_detail,$team_category,$session);
            }
        }
        if(strtolower($team_category->type) == "individual"){
            $qualification_rank = ArcheryScoring::getScoringRankByCategoryId($event_category_id,$score_type,$session);
            return $qualification_rank;
        }
        return [];
    }

    private function teamBestOfThree($category_detail,$team_category,$session)
    {
        $team_cat = ($team_category->id) == "male_team" ? "individu male" : "individu female";
                $category_detail_team = ArcheryEventCategoryDetail::
                where("age_category_id",$category_detail->age_category_id)
                ->where("competition_category_id",$category_detail->competition_category_id)
                ->where("distance_id",$category_detail->distance_id)
                ->where("team_category_id",$team_cat)->first();
                $qualification_rank = ArcheryScoring::getScoringRankByCategoryId($category_detail_team->id,1,$session);
                
                $participant_club =[]; 
                $sequence_club = [];
                $participants = ArcheryEventParticipant::select("archery_event_participants.*","archery_clubs.name as club_name")
                                ->where("event_category_id",$category_detail->id)
                                ->where("status",1)
                                ->leftJoin("archery_clubs","archery_event_participants.club_id","=","archery_clubs.id")->get();
                foreach ($participants as $key => $value) {
                    $club_members = [];
                    $total_per_point = $this->total_per_points;
                    $total = 0;
                    $sequence_club[$value->club_id] = isset($sequence_club[$value->club_id]) ? $sequence_club[$value->club_id] + 1 : 1;
                    foreach ($qualification_rank as $member_rank) {
                        if($value->club_id == $member_rank["club_id"]){
                            if($member_rank["total"]  < 1){
                                continue;
                            }
                            foreach ($member_rank["total_per_points"] as $p => $t) {
                                $total_per_point[$p] = isset($total_per_point[$p]) ? $total_per_point[$p] + $t : $t;
                            }
                            $total = $total + $member_rank["total"];
                            $club_members[] = $member_rank["member"];
                        }
                        if(count($club_members) == 3)
                            break;
                    }
                    $participant_club[] = [
                                            "participant_id"=>$value->id,
                                            "club_id"=>$value->club_id,
                                            "club_name"=>$value->club_name,
                                            "team"=>$value->club_name." - ".$sequence_club[$value->club_id],
                                            "total"=>$total,
                                            "total_x_plus_ten"=>isset($total_per_point["x"]) ? $total_per_point["x"] + $total_per_point["10"] : 0,
                                            "total_x"=>isset($total_per_point["x"]) ? $total_per_point["x"] : 0,
                                            "total_per_points"=>$total_per_point,
                                            "total_tmp"=>ArcheryScoring::getTotalTmp($total_per_point, $total),
                                            "teams"=>$club_members
                                            ];
                }
                usort($participant_club, function($a, $b) {return $b["total_tmp"] > $a["total_tmp"] ? 1 : -1;});
                return $participant_club;
    }

    private function mixTeamBestOfThree($category_detail,$team_category,$session)
    {
                $category_detail_male = ArcheryEventCategoryDetail::
                where("age_category_id",$category_detail->age_category_id)
                ->where("competition_category_id",$category_detail->competition_category_id)
                ->where("distance_id",$category_detail->distance_id)
                ->where("team_category_id","individu male")->first();
                $qualification_male = ArcheryScoring::getScoringRankByCategoryId($category_detail_male->id,1,$session);
                
                $category_detail_female = ArcheryEventCategoryDetail::
                where("age_category_id",$category_detail->age_category_id)
                ->where("competition_category_id",$category_detail->competition_category_id)
                ->where("distance_id",$category_detail->distance_id)
                ->where("team_category_id","individu female")->first();
                $qualification_female = ArcheryScoring::getScoringRankByCategoryId($category_detail_female->id,1,$session);

                $participant_club =[]; 
                $sequence_club = [];
                $participants = ArcheryEventParticipant::select("archery_event_participants.*","archery_clubs.name as club_name")->where("event_category_id",$category_detail->id)
                        ->where("status",1)
                        ->leftJoin("archery_clubs","archery_event_participants.club_id","=","archery_clubs.id")->get();
                foreach ($participants as $key => $value) {
                    $club_members = [];
                    $total_per_point = $this->total_per_points;
                    $total = 0;
                    $sequence_club[$value->club_id] = isset($sequence_club[$value->club_id]) ? $sequence_club[$value->club_id] + 1 : 1;
                    foreach ($qualification_male as $male_rank) {
                        if($value->club_id == $male_rank["club_id"]){
                            if($male_rank["total"]  < 1){
                                continue;
                            }
                            foreach ($male_rank["total_per_points"] as $p => $t) {
                                $total_per_point[$p] = isset($total_per_point[$p]) ? $total_per_point[$p] + $t : $t;
                            }
                            $total = $total + $male_rank["total"];
                            $club_members[] = $male_rank["member"];
                        }
                        if(count($club_members) == 1)
                            break;
                    }
                    foreach ($qualification_female as $female_rank) {
                        if($value->club_id == $female_rank["club_id"]){
                            if($female_rank["total"]  < 1){
                                continue;
                            }
                            foreach ($female_rank["total_per_points"] as $p => $t) {
                                $total_per_point[$p] = isset($total_per_point[$p]) ? $total_per_point[$p] + $t : $t;
                            }
                            $total = $total + $female_rank["total"];
                            $club_members[] = $female_rank["member"];
                        }
                        if(count($club_members) == 2)
                            break;
                    }

                    $participant_club[] = [
                                            "participant_id"=>$value->id,
                                            "club_id"=>$value->club_id,
                                            "club_name"=>$value->club_name,
                                            "team"=>$value->club_name." - ".$sequence_club[$value->club_id],
                                            "total"=>$total,
                                            "total_x_plus_ten"=>isset($total_per_point["x"]) ? $total_per_point["x"] + $total_per_point["10"] : 0,
                                            "total_x"=>isset($total_per_point["x"]) ? $total_per_point["x"] : 0,
                                            "total_per_points"=>$total_per_point,
                                            "total_tmp"=>ArcheryScoring::getTotalTmp($total_per_point, $total),
                                            "teams"=>$club_members
                                            ];
                }
                usort($participant_club, function($a, $b) {return $b["total_tmp"] > $a["total_tmp"] ? 1 : -1;});
                
                return $participant_club;
    }

    protected function validation($parameters)
    {
        return [
        ];
    }
}
