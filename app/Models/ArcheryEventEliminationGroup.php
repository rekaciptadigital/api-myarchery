<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryEventEliminationGroup extends Model
{
    protected $table = 'archery_event_elimination_group';
    protected $guarded = ["id"];

    public static function getMemberCanJoinEliminationGroup($category_detail_group_id, $participant_group_id)
    {

        $category_detail_group = ArcheryEventCategoryDetail::find($category_detail_group_id);
        $participant_group = ArcheryEventParticipant::find($participant_group_id);
        $category_detail_male = ArcheryEventCategoryDetail::where("event_id", $category_detail_group->event_id)
            ->where("age_category_id", $category_detail_group->age_category_id)
            ->where("competition_category_id", $category_detail_group->competition_category_id)
            ->where("distance_id", $category_detail_group->distance_id)
            ->where("team_category_id", "individu male")->first();

        $category_detail_female = ArcheryEventCategoryDetail::where("event_id", $category_detail_group->event_id)
            ->where("age_category_id", $category_detail_group->age_category_id)
            ->where("competition_category_id", $category_detail_group->competition_category_id)
            ->where("distance_id", $category_detail_group->distance_id)
            ->where("team_category_id", "individu female")->first();

        $club_id = $participant_group->club_id;
        // cari semua participant yang ikut kategori beregu yang satu club dengan participant dari param
        $participant_group_order_tema = ArcheryEventParticipant::where("event_category_id", $category_detail_group_id)
            ->where("club_id", $club_id)
            ->where("status", 1)
            ->where("is_present", 1)
            ->get();


        // buat variabel array untuk nampung id
        $member_id_join_elimination_group = [];

        // insertkan member id 
        foreach ($participant_group_order_tema as $p_g_j_e) {
            $elimination_group_member_team =  ArcheryEventEliminationGroupMemberTeam::select("archery_event_elimination_group_member_team.*")->join("archery_event_elimination_group_teams", "archery_event_elimination_group_teams.participant_id", "=", "archery_event_elimination_group_member_team.participant_id")->where("archery_event_elimination_group_member_team.participant_id", $p_g_j_e->id)
                ->get();
            foreach ($elimination_group_member_team as $emt) {
                $member_id_join_elimination_group[] = $emt->member_id;
            }
        }


        if ($category_detail_group->team_category_id == "mix_team") {
            $members_list = ArcheryEventParticipant::select(
                "archery_event_participant_members.id as member_id",
                "users.id as user_id",
                "users.name",
                "archery_event_participants.club_id as club_id",
                "archery_clubs.name as club_name"
            )
                ->join("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id")
                ->join("users", "users.id", "=", "archery_event_participants.user_id")
                ->join("archery_event_participant_members", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
                ->where(function ($query) use ($category_detail_male, $category_detail_female) {
                    return $query->where("archery_event_participants.event_category_id", $category_detail_female->id)
                        ->where("archery_event_participants.event_category_id", $category_detail_male->id);
                })
                ->where("archery_event_participants.status", 1)
                ->where("archery_event_participants.is_present", 1)
                ->where("archery_clubs.id", $club_id)
                ->whereNotIn("archery_event_participant_members.id", $member_id_join_elimination_group)
                ->get();
        } else {
            $team_cat = ($category_detail_group->team_category_id) == "male_team" ? "individu male" : "individu female";
            if ($team_cat == "individu female") {
                $members_list = ArcheryEventParticipant::select(
                    "archery_event_participant_members.id as member_id",
                    "users.id as user_id",
                    "users.name",
                    "archery_event_participants.club_id as club_id",
                    "archery_clubs.name as club_name"
                )
                    ->join("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id")
                    ->join("users", "users.id", "=", "archery_event_participants.user_id")
                    ->join("archery_event_participant_members", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
                    ->where("archery_event_participants.event_category_id", $category_detail_female->id)
                    ->where("archery_event_participants.status", 1)
                    ->where("archery_event_participants.is_present", 1)
                    ->where("archery_clubs.id", $club_id)
                    ->whereNotIn("archery_event_participant_members.id", $member_id_join_elimination_group)
                    ->get();
            } else {
                $members_list = ArcheryEventParticipant::select(
                    "archery_event_participant_members.id as member_id",
                    "users.id as user_id",
                    "users.name",
                    "archery_event_participants.club_id as club_id",
                    "archery_clubs.name as club_name"
                )
                    ->join("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id")
                    ->join("users", "users.id", "=", "archery_event_participants.user_id")
                    ->join("archery_event_participant_members", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
                    ->where("archery_event_participants.event_category_id", $category_detail_male->id)
                    ->where("archery_event_participants.status", 1)
                    ->where("archery_event_participants.is_present", 1)
                    ->where("archery_clubs.id", $club_id)
                    ->whereNotIn("archery_event_participant_members.id", $member_id_join_elimination_group)
                    ->get();
            }
        }

        return $members_list;
    }
}
