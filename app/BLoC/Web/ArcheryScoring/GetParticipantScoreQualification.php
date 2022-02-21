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
                $category_detail_male = ArcheryEventCategoryDetail::
                                        where("age_category_id",$category_detail->age_category_id)
                                        ->where("competition_category_id",$category_detail->competition_category_id)
                                        ->where("distance_id",$category_detail->distance_id)
                                        ->where("team_category_id","individu male")->first();
                $qualification_rank_male = ArcheryScoring::getScoringRankByCategoryId($event_category_id,$score_type,$session);
                return $qualification_rank_male;
            }else{
                return $this->teamBestOfThree($category_detail,$team_category)
            }
        }
        if(strtolower($team_category->type) == "individual"){
            $qualification_rank = ArcheryScoring::getScoringRankByCategoryId($event_category_id,$score_type,$session);
            return $qualification_rank;
        }
        return [];
    }

    private function teamBestOfThree($category_detail,$team_category)
    {
        $team_cat = ($team_category->id) == "male_team" ? "individu male" : "individu female";
                $category_detail_team = ArcheryEventCategoryDetail::
                where("age_category_id",$category_detail->age_category_id)
                ->where("competition_category_id",$category_detail->competition_category_id)
                ->where("distance_id",$category_detail->distance_id)
                ->where("team_category_id",$team_cat)->first();
                $qualification_rank = ArcheryScoring::getScoringRankByCategoryId($category_detail_team->id,$score_type,$session);
                
                $participant_club =[]; 
                $sequence_club = [];
                $participants = ArcheryEventParticipant::select("archery_event_participants.*","archery_clubs.name as club_name")->where("event_category_id",$category_detail->id)
                                ->leftJoin("archery_clubs","archery_event_participants.club_id","=","archery_clubs.id")->get();
                foreach ($participants as $key => $value) {
                    $club_members = [];
                    $total_per_point = [];
                    $total = 0;
                    $sequence_club[$value->club_id] = isset($sequence_club[$value->club_id]) ? $sequence_club[$value->club_id] + 1 : 1;
                    foreach ($qualification_rank as $member_rank) {
                        if($value->club_id == $member_rank["club_id"]){
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
                
                return $participant_club;
    }

    private function mixTeamBestOfThree($category_detail,$team_category)
    {
                $category_detail_male = ArcheryEventCategoryDetail::
                where("age_category_id",$category_detail->age_category_id)
                ->where("competition_category_id",$category_detail->competition_category_id)
                ->where("distance_id",$category_detail->distance_id)
                ->where("team_category_id","individu male")->first();
                $qualification_male = ArcheryScoring::getScoringRankByCategoryId($category_detail_team->id,$score_type,$session);
                
                $category_detail_female = ArcheryEventCategoryDetail::
                where("age_category_id",$category_detail->age_category_id)
                ->where("competition_category_id",$category_detail->competition_category_id)
                ->where("distance_id",$category_detail->distance_id)
                ->where("team_category_id","individu female")->first();
                $qualification_female = ArcheryScoring::getScoringRankByCategoryId($category_detail_team->id,$score_type,$session);

                $participant_club =[]; 
                $sequence_club = [];
                $participants = ArcheryEventParticipant::select("archery_event_participants.*","archery_clubs.name as club_name")->where("event_category_id",$category_detail->id)
                                ->leftJoin("archery_clubs","archery_event_participants.club_id","=","archery_clubs.id")->get();
                foreach ($participants as $key => $value) {
                    $club_members = [];
                    $total_per_point = [];
                    $total = 0;
                    $sequence_club[$value->club_id] = isset($sequence_club[$value->club_id]) ? $sequence_club[$value->club_id] + 1 : 1;
                    foreach ($qualification_rank as $member_rank) {
                        if($value->club_id == $member_rank["club_id"]){
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
                
                return $participant_club;
    }

    protected function validation($parameters)
    {
        return [
        ];
    }
}
