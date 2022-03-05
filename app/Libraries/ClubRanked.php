<?php
namespace App\Libraries;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryClub;
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
            if(!empty($medal_qualification))
                $club_ids[$value->club_id][$medal_qualification] = isset($club_ids[$value->club_id]) && isset($club_ids[$value->club_id][$medal_qualification]) ? $club_ids[$value->club_id][$medal_qualification] + 1 : 1;
            
            $medal_elimination = self::getMedalByPos($value->elimination_ranked);
            if(!empty($medal_elimination))
                $club_ids[$value->club_id][$medal_elimination] = isset($club_ids[$value->club_id]) && isset($club_ids[$value->club_id][$medal_elimination]) ? $club_ids[$value->club_id][$medal_elimination] + 1 : 1;
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

    public static function getMedalByPos($pos){
        $output = "";
        $medal_by_pos = [1=>"gold",2=>"silver",3=>"bronze"];
        if(isset($medal_by_pos[$pos])) $output = $medal_by_pos[$pos];
        return $output;
    }
}