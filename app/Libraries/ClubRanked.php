<?php
namespace App\Libraries;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryClub;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryScoring;
use App\Models\City;
class ClubRanked
{

    public static function getEventRanked($event_id){
        $output = [];
        $club_ids = [];
        $max_pos = 4;
        $members = ArcheryEventParticipantMember::select(
            "archery_event_elimination_members.*","archery_event_participants.club_id")->join("archery_event_participants","archery_event_participant_members.archery_event_participant_id","=","archery_event_participants.id")
            ->join("archery_event_elimination_members","archery_event_participant_members.id","=","archery_event_elimination_members.member_id")                            
            ->where(function ($query) use ($max_pos) {
                return $query->where('archery_event_elimination_members.position_qualification', '<', $max_pos)
                                ->orWhere('archery_event_elimination_members.elimination_ranked', '<', $max_pos);
            })
            ->where("archery_event_participants.event_id",$event_id)
            ->where("archery_event_participants.club_id","!=",0)
            ->where("archery_event_participants.status",1)->get();
        
        foreach ($members as $key => $value) {
            $medal_qualification = self::getMedalByPos($value->position_qualification);
            if(!empty($medal_qualification)){
                $club_ids[$value->club_id][$medal_qualification] = isset($club_ids[$value->club_id]) && isset($club_ids[$value->club_id][$medal_qualification]) ? $club_ids[$value->club_id][$medal_qualification] + 1 : 1;
                $club_ids[$value->club_id]["detail_medal"]["qualification"][$medal_qualification] = isset($club_ids[$value->club_id]) && isset($club_ids[$value->club_id]["detail_medal"]["qualification"][$medal_qualification]) ? $club_ids[$value->club_id]["detail_medal"]["qualification"][$medal_qualification] + 1 : 1;
            }
            
            $medal_elimination = self::getMedalByPos($value->elimination_ranked);
            if(!empty($medal_elimination)){
                $club_ids[$value->club_id][$medal_elimination] = isset($club_ids[$value->club_id]) && isset($club_ids[$value->club_id][$medal_elimination]) ? $club_ids[$value->club_id][$medal_elimination] + 1 : 1;
                $club_ids[$value->club_id]["detail_medal"]["elimination"][$medal_elimination] = isset($club_ids[$value->club_id]) && isset($club_ids[$value->club_id]["detail_medal"]["elimination"][$medal_elimination]) ? $club_ids[$value->club_id]["detail_medal"]["elimination"][$medal_elimination] + 1 : 1;
            }
        }

        
        // TODO SEMENTARA
        $teams = ArcheryEventCategoryDetail::where("event_id",$event_id)->whereIn("team_category_id",["male_team","female_team","mix_team"])->get();

        foreach ($teams as $t => $team) {
            $session = [];
            for ($i=0; $i < $team->session_in_qualification; $i++) { 
                $session[] = $i+1;
            }
            if($team->team_category_id == "mix_team"){
                $mix_ranked = self::getRankedMixTeam($team,$session);
                $mix_pos = 0;
                foreach ($mix_ranked as $mr => $mrank) {
                    $mix_pos = $mix_pos+1;
                    if($mrank["total"] < 1) continue; 

                    $medal_mix_team = self::getMedalByPos($mix_pos);
                    if(!empty($medal_mix_team)){
                        $club_ids[$mrank["club_id"]][$medal_mix_team] = isset($club_ids[$mrank["club_id"]]) && isset($club_ids[$mrank["club_id"]][$medal_mix_team]) ? $club_ids[$mrank["club_id"]][$medal_mix_team] + 1 : 1;
                        $club_ids[$mrank["club_id"]]["detail_medal"]["mix_team_qualification"][$medal_mix_team] = isset($club_ids[$mrank["club_id"]]) && isset($club_ids[$mrank["club_id"]]["detail_medal"]["mix_team_qualification"][$medal_mix_team]) ? $club_ids[$mrank["club_id"]]["detail_medal"]["mix_team_qualification"][$medal_mix_team] + 1 : 1;
                    }
                   
                    if($mix_pos >= 3) break;
                }
            }else{
                $ranked = self::getRankedTeam($team,$session);
                $pos = 0;
                foreach ($ranked as $r => $rank) {
                    $pos = $pos+1;
                    if($rank["total"] < 1) continue; 

                    $medal_team = self::getMedalByPos($pos);
                    if(!empty($medal_team)){
                        $club_ids[$rank["club_id"]][$medal_team] = isset($club_ids[$rank["club_id"]]) && isset($club_ids[$rank["club_id"]][$medal_team]) ? $club_ids[$rank["club_id"]][$medal_team] + 1 : 1;
                        $club_ids[$rank["club_id"]]["detail_medal"]["team_qualification"][$medal_team] = isset($club_ids[$rank["club_id"]]) && isset($club_ids[$rank["club_id"]]["detail_medal"]["team_qualification"][$medal_team]) ? $club_ids[$rank["club_id"]]["detail_medal"]["team_qualification"][$medal_team] + 1 : 1;
                    }
                   
                     if($pos >= 3) break;
                }
                // print_r($ranked);
            }
        }
        
        foreach ($club_ids as $k => $v) {
            $club = ArcheryClub::find($k);
            
            if(!$club) continue;
            
            $city = City::find($club->city);

            $bronze = isset($v["bronze"]) ? $v["bronze"] : 0;
            $gold = isset($v["gold"]) ? $v["gold"] : 0;
            $silver = isset($v["silver"]) ? $v["silver"] : 0;

            $output[] = [
                "club_name" => $club->name,
                "club_logo" => $club->logo,
                "club_city" => $city ? $city->name : "",
                "detail_medal" => $v["detail_medal"],
                "gold" => $gold,
                "silver" => $silver,
                "bronze" => $bronze,
                "total" => $gold + $silver + $bronze
            ];
        }

        usort($output, function ($a, $b) {
            if($a["gold"] == $b["gold"]){
                if($a["silver"] == $b["silver"]){
                    if($a["bronze"] == $b["bronze"]){
                        return -1;
                    }
                    if($a["bronze"] < $b["bronze"]){
                        return 1;
                    }
                }
                if($a["silver"] < $b["silver"]){
                    return 1;
                }
            }
            if($a["gold"] < $b["gold"]){
                return 1;
            }
            return -1;
        });

        return $output;
    }

    public static function getRankedTeam($category_detail,$session){
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
        $team_cat = ($category_detail->team_category_id) == "male_team" ? "individu male" : "individu female";
                $category_detail_team = ArcheryEventCategoryDetail::
                where("event_id",$category_detail->event_id)
                ->where("age_category_id",$category_detail->age_category_id)
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
                    $total_per_point = $total_per_points;
                    $total = 0;
                    $sequence_club[$value->club_id] = isset($sequence_club[$value->club_id]) ? $sequence_club[$value->club_id] + 1 : 1;
                    foreach ($qualification_rank as $k => $member_rank) {
                        if($value->club_id == $member_rank["club_id"]){
                            if($member_rank["total"]  < 1){
                                continue;
                            }
                            foreach ($member_rank["total_per_points"] as $p => $t) {
                                $total_per_point[$p] = isset($total_per_point[$p]) ? $total_per_point[$p] + $t : $t;
                            }
                            $total = $total + $member_rank["total"];
                            $club_members[] = $member_rank["member"];
                            unset($qualification_rank[$k]);
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

    public static function getRankedMixTeam($category_detail,$session){
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
        $category_detail_male = ArcheryEventCategoryDetail::
                where("event_id",$category_detail->event_id)
                ->where("age_category_id",$category_detail->age_category_id)
                ->where("competition_category_id",$category_detail->competition_category_id)
                ->where("distance_id",$category_detail->distance_id)
                ->where("team_category_id","individu male")->first();
                $qualification_male = ArcheryScoring::getScoringRankByCategoryId($category_detail_male->id,1,$session);
                
                $category_detail_female = ArcheryEventCategoryDetail::
                where("event_id",$category_detail->event_id)
                ->where("age_category_id",$category_detail->age_category_id)
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
                    $total_per_point = $total_per_points;
                    $total = 0;
                    $sequence_club[$value->club_id] = isset($sequence_club[$value->club_id]) ? $sequence_club[$value->club_id] + 1 : 1;
                    foreach ($qualification_male as $k => $male_rank) {
                        if($value->club_id == $male_rank["club_id"]){
                            if($male_rank["total"]  < 1){
                                continue;
                            }
                            foreach ($male_rank["total_per_points"] as $p => $t) {
                                $total_per_point[$p] = isset($total_per_point[$p]) ? $total_per_point[$p] + $t : $t;
                            }
                            $total = $total + $male_rank["total"];
                            $club_members[] = $male_rank["member"];
                            unset($qualification_male[$k]);
                        }
                        if(count($club_members) == 1)
                            break;
                    }
                    foreach ($qualification_female as $ky => $female_rank) {
                        if($value->club_id == $female_rank["club_id"]){
                            if($female_rank["total"]  < 1){
                                continue;
                            }
                            foreach ($female_rank["total_per_points"] as $p => $t) {
                                $total_per_point[$p] = isset($total_per_point[$p]) ? $total_per_point[$p] + $t : $t;
                            }
                            $total = $total + $female_rank["total"];
                            $club_members[] = $female_rank["member"];
                            unset($qualification_female[$ky]);
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

    public static function getMedalByPos($pos){
        $output = "";
        $medal_by_pos = [1=>"gold",2=>"silver",3=>"bronze"];
        if(isset($medal_by_pos[$pos])) $output = $medal_by_pos[$pos];
        return $output;
    }
}