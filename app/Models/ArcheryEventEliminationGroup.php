<?php

namespace App\Models;

use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Database\Eloquent\Model;

class ArcheryEventEliminationGroup extends Model
{
    protected $table = 'archery_event_elimination_group';
    protected $guarded = ["id"];

    public static function getMemberCanJoinEliminationGroup($category_detail_group_id, $participant_group_id)
    {
        $category_detail_group = ArcheryEventCategoryDetail::find($category_detail_group_id);
        $event = ArcheryEvent::find($category_detail_group->event_id);
        $participant_group = ArcheryEventParticipant::find($participant_group_id);
        $category_detail_male = ArcheryEventCategoryDetail::where("event_id", $category_detail_group->event_id)
            ->where("age_category_id", $category_detail_group->age_category_id)
            ->where("competition_category_id", $category_detail_group->competition_category_id)
            ->where("distance_id", $category_detail_group->distance_id)
            ->where("team_category_id", "individu male")
            ->first();

        $category_detail_female = ArcheryEventCategoryDetail::where("event_id", $category_detail_group->event_id)
            ->where("age_category_id", $category_detail_group->age_category_id)
            ->where("competition_category_id", $category_detail_group->competition_category_id)
            ->where("distance_id", $category_detail_group->distance_id)
            ->where("team_category_id", "individu female")
            ->first();
        // cari semua participant yang ikut kategori beregu yang satu club dengan participant dari param
        $participant_group_order_team = ArcheryEventParticipant::where("event_category_id", $category_detail_group_id);

        if ($event->parent_classification == 1) {
            $participant_group_order_team = $participant_group_order_team->where("club_id", $participant_group->club_id);
        } elseif ($event->parent_classification == 2) {
            $participant_group_order_team = $participant_group_order_team->where("classification_country_id", $participant_group->classification_country_id);
        } elseif ($event->parent_classification == 3) {
            $participant_group_order_team = $participant_group_order_team->where("classification_province_id", $participant_group->classification_province_id);
        } elseif ($event->parent_classification == 4) {
            $participant_group_order_team = $participant_group_order_team->where("city_id", $participant_group->city_id);
        } else {
            $participant_group_order_team = $participant_group_order_team->where("children_classification_id", $participant_group->children_classification_id);
        }

        $participant_group_order_team = $participant_group_order_team->where("status", 1)
            ->where("is_present", 1)
            ->get();


        // buat variabel array untuk nampung id
        $member_id_join_elimination_group = [];

        // insertkan member id 
        foreach ($participant_group_order_team as $p_g_j_e) {
            $elimination_group_member_team =  ArcheryEventEliminationGroupMemberTeam::select("archery_event_elimination_group_member_team.*")
                ->join("archery_event_elimination_group_teams", "archery_event_elimination_group_teams.participant_id", "=", "archery_event_elimination_group_member_team.participant_id")
                ->where("archery_event_elimination_group_member_team.participant_id", $p_g_j_e->id)
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
                "archery_clubs.name as club_name",
                "archery_event_participants.classification_country_id as country_id",
                "countries.name as country_name",
                "archery_event_participants.classification_province_id as province_id",
                $event->classification_country_id == 102 ? "provinces.name as province_name" : "states.name as province_name",
                "archery_event_participants.city_id",
                $event->classification_country_id == 102 ? "cities.name as city_name" : "cities_of_countries.name as city_name",
                "archery_event_participants.children_classification_id",
                "children_classification_members.title as children_classification_members_name"
            )
                ->join("users", "users.id", "=", "archery_event_participants.user_id")
                ->join("archery_event_participant_members", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id");

            // jika mewakili club
            $members_list = $members_list->leftJoin("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id");
            // jika mewakili negara
            $members_list = $members_list->leftJoin("countries", "countries.id", "=", "archery_event_participants.classification_country_id");
            // jika mewakili provinsi
            if ($event->classification_country_id == 102) {
                $members_list = $members_list->leftJoin("provinces", "provinces.id", "=", "archery_event_participants.classification_province_id");
            } else {
                $members_list = $members_list->leftJoin("states", "states.id", "=", "archery_event_participants.classification_province_id");
            }
            // jika mewakili kota
            if ($event->classification_country_id == 102) {
                $members_list = $members_list->leftJoin("cities", "cities.id", "=", "archery_event_participants.city_id");
            } else {
                $members_list = $members_list->leftJoin("cities_of_countries", "cities_of_countries.id", "=", "archery_event_participants.city_id");
            }
            // jika berasal dari settingan admin
            $members_list = $members_list->leftJoin("children_classification_members", "children_classification_members.id", "=", "archery_event_participants.children_classification_id");

            $members_list = $members_list->where(function ($query) use ($category_detail_male, $category_detail_female) {
                return $query->where("archery_event_participants.event_category_id", $category_detail_female->id)
                    ->orWhere("archery_event_participants.event_category_id", $category_detail_male->id);
            })->where("archery_event_participants.status", 1)
                ->where("archery_event_participants.is_present", 1);

            if ($event->parent_classification == 1) {
                $members_list = $members_list->where("archery_event_participants.club_id", $participant_group->club_id);
            } elseif ($event->parent_classification == 2) {
                $members_list = $members_list->where("archery_event_participants.classification_country_id", $participant_group->classification_country_id);
            } elseif ($event->parent_classification == 3) {
                $members_list = $members_list->where("archery_event_participants.classification_province_id", $participant_group->classification_province_id);
            } elseif ($event->parent_classification == 4) {
                $members_list = $members_list->where("archery_event_participants.city_id", $participant_group->city_id);
            } else {
                $members_list = $members_list->where("archery_event_participants.children_classification_id", $participant_group->children_classification_id);
            }

            $members_list = $members_list->whereNotIn("archery_event_participant_members.id", $member_id_join_elimination_group)
                ->get();
        } else {
            $team_cat = ($category_detail_group->team_category_id) == "male_team" ? "individu male" : "individu female";
            if ($team_cat == "individu female") {
                $members_list = ArcheryEventParticipant::select(
                    "archery_event_participant_members.id as member_id",
                    "users.id as user_id",
                    "users.name",
                    "archery_event_participants.club_id as club_id",
                    "archery_clubs.name as club_name",
                    "archery_event_participants.classification_country_id as country_id",
                    "countries.name as country_name",
                    "archery_event_participants.classification_province_id as province_id",
                    $event->classification_country_id == 102 ? "provinces.name as province_name" : "states.name as province_name",
                    "archery_event_participants.city_id",
                    $event->classification_country_id == 102 ? "cities.name as city_name" : "cities_of_countries.name as city_name",
                    "archery_event_participants.children_classification_id",
                    "children_classification_members.title as children_classification_members_name"
                )
                    ->join(
                        "users",
                        "users.id",
                        "=",
                        "archery_event_participants.user_id"
                    )->join(
                        "archery_event_participant_members",
                        "archery_event_participant_members.archery_event_participant_id",
                        "=",
                        "archery_event_participants.id"
                    );

                // jika mewakili club
                $members_list = $members_list->leftJoin("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id");
                // jika mewakili negara
                $members_list = $members_list->leftJoin("countries", "countries.id", "=", "archery_event_participants.classification_country_id");
                // jika mewakili provinsi
                if ($event->classification_country_id == 102) {
                    $members_list = $members_list->leftJoin("provinces", "provinces.id", "=", "archery_event_participants.classification_province_id");
                } else {
                    $members_list = $members_list->leftJoin("states", "states.id", "=", "archery_event_participants.classification_province_id");
                }
                // jika mewakili kota
                if ($event->classification_country_id == 102) {
                    $members_list = $members_list->leftJoin("cities", "cities.id", "=", "archery_event_participants.city_id");
                } else {
                    $members_list = $members_list->leftJoin("cities_of_countries", "cities_of_countries.id", "=", "archery_event_participants.city_id");
                }
                // jika berasal dari settingan admin
                $members_list = $members_list->leftJoin("children_classification_members", "children_classification_members.id", "=", "archery_event_participants.children_classification_id");

                $members_list = $members_list->where("archery_event_participants.event_category_id", $category_detail_female->id)
                    ->where("archery_event_participants.status", 1)
                    ->where("archery_event_participants.is_present", 1);

                if ($event->parent_classification == 1) {
                    $members_list = $members_list->where("archery_event_participants.club_id", $participant_group->club_id);
                } elseif ($event->parent_classification == 2) {
                    $members_list = $members_list->where("archery_event_participants.classification_country_id", $participant_group->classification_country_id);
                } elseif ($event->parent_classification == 3) {
                    $members_list = $members_list->where("archery_event_participants.classification_province_id", $participant_group->classification_province_id);
                } elseif ($event->parent_classification == 4) {
                    $members_list = $members_list->where("archery_event_participants.city_id", $participant_group->city_id);
                } else {
                    $members_list = $members_list->where("archery_event_participants.children_classification_id", $participant_group->children_classification_id);
                }

                $members_list = $members_list->whereNotIn("archery_event_participant_members.id", $member_id_join_elimination_group)
                    ->get();
            } elseif ($team_cat == "individu male") {
                $members_list = ArcheryEventParticipant::select(
                    "archery_event_participant_members.id as member_id",
                    "users.id as user_id",
                    "users.name",
                    "archery_event_participants.club_id as club_id",
                    "archery_clubs.name as club_name",
                    "archery_event_participants.classification_country_id as country_id",
                    "countries.name as country_name",
                    "archery_event_participants.classification_province_id as province_id",
                    $event->classification_country_id == 102 ? "provinces.name as province_name" : "states.name as province_name",
                    "archery_event_participants.city_id",
                    $event->classification_country_id == 102 ? "cities.name as city_name" : "cities_of_countries.name as city_name",
                    "archery_event_participants.children_classification_id",
                    "children_classification_members.title as children_classification_members_name"
                )->join(
                    "users",
                    "users.id",
                    "=",
                    "archery_event_participants.user_id"
                )->join(
                    "archery_event_participant_members",
                    "archery_event_participant_members.archery_event_participant_id",
                    "=",
                    "archery_event_participants.id"
                );

                // jika mewakili club
                $members_list = $members_list->leftJoin("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id");
                // jika mewakili negara
                $members_list = $members_list->leftJoin("countries", "countries.id", "=", "archery_event_participants.classification_country_id");
                // jika mewakili provinsi
                if ($event->classification_country_id == 102) {
                    $members_list = $members_list->leftJoin("provinces", "provinces.id", "=", "archery_event_participants.classification_province_id");
                } else {
                    $members_list = $members_list->leftJoin("states", "states.id", "=", "archery_event_participants.classification_province_id");
                }
                // jika mewakili kota
                if ($event->classification_country_id == 102) {
                    $members_list = $members_list->leftJoin("cities", "cities.id", "=", "archery_event_participants.city_id");
                } else {
                    $members_list = $members_list->leftJoin("cities_of_countries", "cities_of_countries.id", "=", "archery_event_participants.city_id");
                }
                // jika berasal dari settingan admin
                $members_list = $members_list->leftJoin("children_classification_members", "children_classification_members.id", "=", "archery_event_participants.children_classification_id");

                $members_list = $members_list->where("archery_event_participants.event_category_id", $category_detail_male->id)
                    ->where("archery_event_participants.status", 1)
                    ->where("archery_event_participants.is_present", 1);

                if ($event->parent_classification == 1) {
                    $members_list = $members_list->where("archery_event_participants.club_id", $participant_group->club_id);
                } elseif ($event->parent_classification == 2) {
                    $members_list = $members_list->where("archery_event_participants.classification_country_id", $participant_group->classification_country_id);
                } elseif ($event->parent_classification == 3) {
                    $members_list = $members_list->where("archery_event_participants.classification_province_id", $participant_group->classification_province_id);
                } elseif ($event->parent_classification == 4) {
                    $members_list = $members_list->where("archery_event_participants.city_id", $participant_group->city_id);
                } else {
                    $members_list = $members_list->where("archery_event_participants.children_classification_id", $participant_group->children_classification_id);
                }

                $members_list = $members_list->whereNotIn("archery_event_participant_members.id", $member_id_join_elimination_group)
                    ->get();
            } else {
                throw new BLoCException("team category invalid");
            }
        }

        return $members_list;
    }
}
