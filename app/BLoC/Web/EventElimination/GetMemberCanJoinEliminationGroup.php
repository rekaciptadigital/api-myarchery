<?php

namespace App\BLoC\Web\EventElimination;

use App\Models\ArcheryScoring;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationSchedule;
use DAI\Utils\Abstracts\Transactional;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventEliminationGroupMatch;
use App\Models\ArcheryEventEliminationGroupMemberTeam;
use App\Models\ArcheryEventEliminationGroupTeams;
use DAI\Utils\Exceptions\BLoCException;
use App\Models\ArcheryEventEliminationMember;
use App\Models\ArcherySeriesUserPoint;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventMasterCompetitionCategory;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryMasterTeamCategory;

class GetMemberCanJoinEliminationGroup extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $category_detail_group_id = $parameters->get("category_detail_group_id");
        $participant_id = $parameters->get("participant_id");
        $category_detail_group = ArcheryEventCategoryDetail::find($category_detail_group_id);
        if (!$category_detail_group) {
            throw new BLoCException("category not found");
        }


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
        if ($category_detail_group->team_category_id == "mix_team") {
        } else {
            // dapatkan participant dari param
            $participant_group = ArcheryEventParticipant::find($participant_id);

            $club_id = $participant_group->club_id;
            // cari semua participant yang ikut kategori beregu yang satu club dengan participant dari param
            $participant_group_order_tema = ArcheryEventParticipant::where("event_category_id", $category_detail_group_id)
                ->where("club_id", $club_id)
                ->where("status", 1)
                ->where("is_present", 1)
                ->get();

            // return $participant_group_order_tema;

            // buat variabel array untuk nampung id
            $member_id_join_elimination_group = [];
            $egmt = [];

            // insertkan member id 
            foreach ($participant_group_order_tema as $p_g_j_e) {
                $elimination_group_member_team =  ArcheryEventEliminationGroupMemberTeam::select("archery_event_elimination_group_member_team.*")->join("archery_event_elimination_group_teams", "archery_event_elimination_group_teams.participant_id", "=", "archery_event_elimination_group_member_team.participant_id")->where("archery_event_elimination_group_member_team.participant_id", $p_g_j_e->id)
                    ->get();
                foreach ($elimination_group_member_team as $emt) {
                    $member_id_join_elimination_group[] = $emt->member_id;
                }
            }


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
            return $members_list;
        }
    }

    protected function validation($parameters)
    {
        return [
            "category_detail_group_id" => "required",
            "participant_id" => "required"
        ];
    }

    // private function makeTemplateIndividu($category_id, $score_type, $session, $elimination_member_count, $match_type, $type_scoring)
    // {
    //     $qualification_rank = ArcheryScoring::getScoringRankByCategoryId($category_id, $score_type, $session, false, null, true);

    //     // cek apakah terdapat peserta yang belum melakukan shoot qualifikasi
    //     if (count($qualification_rank) > 0) {
    //         foreach ($qualification_rank as $key => $value) {
    //             // if ($value["total"] == 0) {
    //             //     throw new BLoCException("skor kualifikasi masih kosong");
    //             // }

    //             foreach ($session as $key => $s) {
    //                 // if ($value["sessions"][$s]["total"] == 0) {
    //                 //     throw new BLoCException("terdapat peserta yang belum melakukan shoot kualifikasi secara lengkap");
    //                 // }
    //                 $scoring_per_session =  ArcheryScoring::where("participant_member_id", $value["member"]->id)
    //                     ->where("type", 1)
    //                     ->where("scoring_session", $s)
    //                     ->first();
    //                 if (!$scoring_per_session) {
    //                     throw new BLoCException("terdapat peserta yang belum melakukan shoot kualifikasi secara lengkap");
    //                 }
    //             }
    //         }
    //     }

    //     // cek apakah ada yang telah melakukan shoot di eliminasi
    //     $participant_collection_have_shoot_off = ArcheryScoring::select(
    //         "archery_event_participant_members.*",
    //     )
    //         ->join("archery_event_participant_members", "archery_event_participant_members.id", "=", "archery_scorings.participant_member_id")
    //         ->join("archery_event_participants", "archery_event_participants.id", "=", "archery_event_participant_members.archery_event_participant_id")
    //         ->where('archery_event_participants.status', 1)
    //         ->where('archery_event_participants.event_category_id', $category_id)
    //         ->where("archery_event_participant_members.have_shoot_off", 1)
    //         ->distinct()
    //         ->get();

    //     $participant_collection_score_elimination = ArcheryScoring::select(
    //         "archery_event_participant_members.*",
    //     )
    //         ->join("archery_event_participant_members", "archery_event_participant_members.id", "=", "archery_scorings.participant_member_id")
    //         ->join("archery_event_participants", "archery_event_participants.id", "=", "archery_event_participant_members.archery_event_participant_id")
    //         ->where('archery_event_participants.status', 1)
    //         ->where('archery_event_participants.event_category_id', $category_id)
    //         ->where("archery_scorings.type", 2)
    //         ->distinct()
    //         ->get();


    //     if ($participant_collection_score_elimination->count() > 0) {
    //         throw new BLoCException("sudah ada yang melakukan eliminasi");
    //     }

    //     if ($participant_collection_have_shoot_off->count() > 0) {
    //         throw new BLoCException("masih terdapat peserta yang harus melakukan shoot off");
    //     }

    //     $template = ArcheryEventEliminationSchedule::makeTemplate($qualification_rank, $elimination_member_count);

    //     $elimination = ArcheryEventElimination::where("event_category_id", $category_id)->first();
    //     if ($elimination) {
    //         throw new BLoCException("elimination sudah ditentukan");
    //     }
    //     $elimination = new ArcheryEventElimination;
    //     $elimination->event_category_id = $category_id;
    //     $elimination->count_participant = $elimination_member_count;
    //     $elimination->elimination_type = $match_type;
    //     $elimination->elimination_scoring_type = $type_scoring;
    //     $elimination->gender = "none";
    //     $elimination->save();

    //     foreach ($template as $key => $value) {
    //         foreach ($value["seeds"] as $k => $v) {
    //             foreach ($v["teams"] as $i => $team) {
    //                 $elimination_member_id = 0;
    //                 $member_id = isset($team["id"]) ? $team["id"] : 0;
    //                 $thread = $k;
    //                 $position_qualification = isset($team["postition"]) ? $team["postition"] : 0;
    //                 if ($member_id != 0) {
    //                     $em = ArcheryEventEliminationMember::where("member_id", $member_id)->first();
    //                     if ($em) {
    //                         $elimination_member = $em;
    //                     } else {
    //                         $elimination_member = new ArcheryEventEliminationMember;
    //                         $elimination_member->thread = $thread;
    //                         $elimination_member->member_id = $member_id;
    //                         $elimination_member->position_qualification = $position_qualification;
    //                         $elimination_member->save();
    //                     }
    //                     $elimination_member_id = $elimination_member->id;
    //                 }

    //                 $match = new ArcheryEventEliminationMatch;
    //                 $match->event_elimination_id = $elimination->id;
    //                 $match->elimination_member_id = $elimination_member_id;
    //                 $match->elimination_schedule_id = 0;
    //                 $match->round = $key + 1;
    //                 $match->match = $k + 1;
    //                 $match->index = $i;
    //                 if (isset($team["win"]))
    //                     $match->win = $team["win"];

    //                 $match->gender = "none";
    //                 $match->save();
    //             }
    //         }
    //     }
    //     ArcherySeriesUserPoint::setMemberQualificationPoint($category_id);

    //     return $template;
    // }

    // private function makeTemplateTeam($group, $category_team, $elimination_member_count, $scoring_type)
    // {
    //     if ($group == "mix_team") {
    //         $lis_team = ArcheryScoring::mixTeamBestOfThree($category_team);
    //         $template = ArcheryEventEliminationSchedule::makeTemplateTeam($lis_team, $elimination_member_count);
    //     } else {
    //         $team_cat = ($group) == "male_team" ? "individu male" : "individu female";
    //         $category_detail_individu = ArcheryEventCategoryDetail::where("event_id", $category_team->event_id)
    //             ->where("age_category_id", $category_team->age_category_id)
    //             ->where("competition_category_id", $category_team->competition_category_id)
    //             ->where("distance_id", $category_team->distance_id)
    //             ->where("team_category_id", $team_cat)
    //             ->first();

    //         if (!$category_detail_individu) {
    //             throw new BLoCException("category individu tidak ditemukan");
    //         }

    //         $lis_team = ArcheryScoring::teamBestOfThree($category_detail_individu->id, $category_detail_individu->session_in_qualification, $category_team->id);
    //         $template = ArcheryEventEliminationSchedule::makeTemplateTeam($lis_team, $elimination_member_count);
    //     }

    //     $elimination_group = ArcheryEventEliminationGroup::where("category_id", $category_team->id)->first();
    //     if ($elimination_group) {
    //         throw new BLoCException("elimination sudah ditentukan");
    //     }
    //     $elimination_group = new ArcheryEventEliminationGroup;
    //     $elimination_group->category_id = $category_team->id;
    //     $elimination_group->count_participant = $elimination_member_count;
    //     $elimination_group->elimination_scoring_type = $scoring_type;
    //     $elimination_group->save();

    //     if (count($lis_team) > 0) {
    //         foreach ($lis_team as $value1) {
    //             if (count($value1["teams"]) > 0) {
    //                 foreach ($value1["teams"] as $value2) {
    //                     ArcheryEventEliminationGroupMemberTeam::create([
    //                         "participant_id" => $value1["participant_id"],
    //                         "member_id" => $value2["id"]
    //                     ]);
    //                 }
    //             }
    //         }
    //     }

    //     foreach ($template as $key => $value) {
    //         foreach ($value["seeds"] as $k => $v) {
    //             foreach ($v["teams"] as $i => $team) {
    //                 $elimination_group_team_id = 0;
    //                 $participant_id = isset($team["participant_id"]) ? $team["participant_id"] : 0;
    //                 $thread = $k;
    //                 $position = isset($team["postition"]) ? $team["postition"] : 0;
    //                 if ($participant_id != 0) {
    //                     $team_name = "";
    //                     foreach ($lis_team as $lt) {
    //                         if ($lt["participant_id"] == $participant_id) {
    //                             $team_name = $lt["team"];
    //                         }
    //                     }
    //                     $em = ArcheryEventEliminationGroupTeams::where("participant_id", $participant_id)->first();
    //                     if ($em) {
    //                         $elimination_team = $em;
    //                     } else {
    //                         $elimination_team = new ArcheryEventEliminationGroupTeams;
    //                         $elimination_team->thread = $thread;
    //                         $elimination_team->participant_id = $participant_id;
    //                         $elimination_team->position = $position;
    //                         $elimination_team->team_name = $team_name;
    //                         $elimination_team->save();
    //                     }
    //                     $elimination_group_team_id = $elimination_team->id;
    //                 }

    //                 $match = new ArcheryEventEliminationGroupMatch;
    //                 $match->elimination_group_id = $elimination_group->id;
    //                 $match->group_team_id = $elimination_group_team_id;
    //                 $match->round = $key + 1;
    //                 $match->match = $k + 1;
    //                 $match->index = $i;
    //                 if (isset($team["win"])) {
    //                     $match->win = $team["win"];
    //                 }
    //                 $match->save();
    //             }
    //         }
    //     }
    //     return $template;
    // }
}
