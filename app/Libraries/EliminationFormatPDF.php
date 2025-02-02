<?php

namespace App\Libraries;

use App\Models\ArcheryEvent;
use App\Models\ArcheryScoring;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventEliminationSchedule;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventEliminationGroupMatch;
use App\Models\ArcheryScoringEliminationGroup;
use App\Models\ArcheryEventEliminationGroupMemberTeam;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Exceptions\BLoCException;

class EliminationFormatPDF
{
    public static function getDataGraph($event_category_id, ArcheryEvent $event)
    {
        $parent_classifification_id = $event->parent_classification;
        $elimination = ArcheryEventElimination::where("event_category_id", $event_category_id)->first();
        $elimination_id = 0;
        if ($elimination) {
            $elimination_member_count = $elimination->count_participant;
            $elimination_id = $elimination->id;
        }

        $category = ArcheryEventCategoryDetail::find($event_category_id);
        $score_type = 1; // 1 for type qualification
        $session = $category->getArraySessionCategory();

        $fix_members = ArcheryEventEliminationMatch::select(
            "archery_event_elimination_members.position_qualification",
            "archery_event_participant_members.archery_event_participant_id as participant_id",
            "users.name",
            "archery_event_participant_members.id AS member_id",
            "archery_event_participant_members.club",
            "archery_event_participant_members.gender",
            "archery_event_elimination_matches.id",
            "archery_event_elimination_matches.result",
            "archery_event_elimination_matches.round",
            "archery_event_elimination_matches.match",
            "archery_event_elimination_matches.win",
            "archery_event_elimination_schedules.date",
            "archery_event_elimination_schedules.start_time",
            "archery_event_elimination_schedules.end_time"
        )
            ->leftJoin("archery_event_elimination_members", "archery_event_elimination_matches.elimination_member_id", "=", "archery_event_elimination_members.id")
            ->leftJoin("archery_event_participant_members", "archery_event_elimination_members.member_id", "=", "archery_event_participant_members.id")
            ->leftJoin("users", "users.id", "=", "archery_event_participant_members.user_id")
            ->leftJoin("archery_event_elimination_schedules", "archery_event_elimination_matches.elimination_schedule_id", "=", "archery_event_elimination_schedules.id")
            ->where("archery_event_elimination_matches.event_elimination_id", $elimination_id)->get();
        $qualification_rank = [];
        $updated = true;

        if (count($fix_members) > 0) {
            $members = [];
            foreach ($fix_members as $key => $value) {
                $members[$value->round][$value->match]["date"] = $value->date . " " . $value->start_time . " - " . $value->end_time;
                if ($value->name != null) {

                    $contingent =  ArcheryEventParticipant::select(
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
                    );
                    // jika mewakili club
                    $contingent = $contingent->leftJoin("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id");


                    // jika mewakili negara
                    $contingent = $contingent->leftJoin("countries", "countries.id", "=", "archery_event_participants.classification_country_id");


                    // jika mewakili provinsi
                    if ($event->classification_country_id == 102) {
                        $contingent = $contingent->leftJoin("provinces", "provinces.id", "=", "archery_event_participants.classification_province_id");
                    } else {
                        $contingent = $contingent->leftJoin("states", "states.id", "=", "archery_event_participants.classification_province_id");
                    }

                    // jika mewakili kota
                    if ($event->classification_country_id == 102) {
                        $contingent = $contingent->leftJoin("cities", "cities.id", "=", "archery_event_participants.city_id");
                    } else {
                        $contingent = $contingent->leftJoin("cities_of_countries", "cities_of_countries.id", "=", "archery_event_participants.city_id");
                    }

                    // jika berasal dari settingan admin
                    $contingent = $contingent->leftJoin("children_classification_members", "children_classification_members.id", "=", "archery_event_participants.children_classification_id");

                    $contingent = $contingent->where("archery_event_participants.id", $value->participant_id)
                        ->where("archery_event_participants.status", 1)
                        ->first();

                    $members[$value->round][$value->match]["teams"][] = array(
                        "id" => $value->member_id,
                        "name" => $value->name,
                        "gender" => $value->gender,
                        "club" => $value->club,
                        "potition" => $value->position_qualification,
                        "win" => $value->win,
                        "result" => $value->result,
                        "status" => $value->win == 1 ? "win" : "wait",
                        "club_id" => $contingent->club_id,
                        "club_name" => $contingent->club_name,
                        "country_id" => $contingent->country_id,
                        "country_name" => $contingent->country_name,
                        "province_id" => $contingent->province_id,
                        "province_name" => $contingent->province_name,
                        "city_id" => $contingent->city_id,
                        "city_name" => $contingent->city_name,
                        "children_classification_id" => $contingent->children_classification_id,
                        "children_classification_members_name" => $contingent->children_classification_members_name,
                        "parent_classification_type" => $parent_classifification_id
                    );
                } else {
                    $members[$value->round][$value->match]["teams"][] = ["status" => "bye"];
                }
            }

            $fix_members = $members;
            $updated = false;
            $template["rounds"] = ArcheryEventEliminationSchedule::getTemplate($fix_members, $elimination_member_count);
        } else {
            $qualification_rank = ArcheryScoring::getScoringRankByCategoryId($event_category_id, $score_type, $session, false, null, false, 1);
            $template["rounds"] = ArcheryEventEliminationSchedule::makeTemplate($qualification_rank, $elimination_member_count);
        }
        // $template["rounds"] = ArcheryEventEliminationSchedule::makeTemplate2($qualification_rank, $elimination_member_count, $match_type, $event_category_id, $gender, $fix_members);
        $template["updated"] = $updated;
        $template["elimination_id"] = $elimination_id;
        return $template;
    }

    public static function getDataGraphTeam($category_team)
    {
        $elimination = ArcheryEventEliminationGroup::where("category_id", $category_team->id)->first();
        $elimination_id = 0;
        $elimination_member_count = $category_team->default_elimination_count;
        if ($elimination) {
            $elimination_id = $elimination->id;
        }

        $fix_teams_1 = ArcheryEventEliminationGroupMatch::select(
            "archery_event_elimination_group_teams.position",
            "archery_event_elimination_group_teams.participant_id",
            "archery_event_elimination_group_teams.team_name",
            "archery_event_elimination_group_match.id",
            "archery_event_elimination_group_match.round",
            "archery_event_elimination_group_match.match",
            "archery_event_elimination_group_match.win",
            "archery_event_elimination_group_match.bud_rest",
            "archery_event_elimination_group_match.target_face",
            "archery_scoring_elimination_group.result",
            "archery_scoring_elimination_group.scoring_detail",
            "archery_event_elimination_group_match.elimination_group_id"
        )
            ->leftJoin("archery_event_elimination_group_teams", "archery_event_elimination_group_match.group_team_id", "=", "archery_event_elimination_group_teams.id")
            ->leftJoin("archery_scoring_elimination_group", "archery_scoring_elimination_group.elimination_match_group_id", "=", "archery_event_elimination_group_match.id")
            ->where("archery_event_elimination_group_match.elimination_group_id", $elimination_id)
            ->orderBy("archery_event_elimination_group_match.round")
            ->orderBy("archery_event_elimination_group_match.match")
            ->orderBy("archery_event_elimination_group_match.index")
            ->get();

        $lis_team = [];

        $updated = true;
        if ($fix_teams_1->count() > 0) {
            $teams = [];
            foreach ($fix_teams_1 as $key => $value) {
                $teams[$value->round][$value->match]["date"] = $value->date . " " . $value->start_time . " - " . $value->end_time;
                if ($value->participant_id != null) {
                    $archery_scooring_team = ArcheryScoringEliminationGroup::where("elimination_match_group_id", $value->id)->first();
                    $admin_total = 0;
                    $is_different = 0;
                    $total_scoring = 0;
                    if ($archery_scooring_team) {
                        $admin_total = $archery_scooring_team->admin_total;
                        $scoring_detail = json_decode($archery_scooring_team->scoring_detail);
                        $total_scoring = $scoring_detail->result;
                        if ($total_scoring != $admin_total) {
                            $is_different = 1;
                        }
                    }
                    $list_member = [];
                    $list_group_team = ArcheryEventEliminationGroupMemberTeam::where("participant_id", $value->participant_id)->get();
                    if ($list_group_team->count() > 0) {
                        foreach ($list_group_team as $gt) {
                            $m = ArcheryEventParticipantMember::select("archery_event_participant_members.user_id as user_id", "archery_event_participant_members.id as member_id", "users.name")
                                ->join("users", "users.id", "=", "archery_event_participant_members.user_id")
                                ->where("archery_event_participant_members.id", $gt->member_id)
                                ->first();

                            $list_member[] = $m;
                        }
                    }

                    $team_name = $value->team_name;

                    $teams[$value->round][$value->match]["teams"][] = array(
                        "participant_id" => $value->participant_id,
                        "match_id" => $value->id,
                        "potition" => $value->position,
                        "win" => $value->win,
                        "result" => $total_scoring,
                        "status" => $value->win == 1 ? "win" : "wait",
                        "admin_total" => $admin_total,
                        "budrest_number" => $value->bud_rest != 0 && $value->target_face != "" ? $value->bud_rest . "" . $value->target_face : "",
                        "is_different" => $is_different,
                        "member_team" => $list_member,
                        "team_name" => $team_name
                    );
                } else {
                    $match = ArcheryEventEliminationGroupMatch::where("elimination_group_id", $elimination_id)->where("round", $value->round)->where("match", $value->match)->get();
                    if ($match[0]->group_team_id == 0 && $match[1]->win == 1) {
                        $teams[$value->round][$value->match]["teams"][] = ["status" => "bye"];
                    } elseif ($match[1]->group_team_id == 0 && $match[0]->win == 1) {
                        $teams[$value->round][$value->match]["teams"][] = ["status" => "bye"];
                    } elseif (($match[0]->group_team_id == 0 && $match[1]->group_team_id == 0) && $value->round == 1) {
                        $teams[$value->round][$value->match]["teams"][] = ["status" => "bye"];
                    } else {
                        $teams[$value->round][$value->match]["teams"][] = ["status" => "wait"];
                    }
                }
            }

            $fix_team_2 = $teams;
            $updated = false;
            $template["rounds"] = ArcheryEventEliminationSchedule::getTemplate($fix_team_2, $elimination_member_count);
        } else {
            if ($category_team->team_category_id == "mix_team") {
                $lis_team = ArcheryEventParticipant::mixTeamBestOfThree($category_team);
            } else {
                $lis_team = ArcheryEventParticipant::teamBestOfThree($category_team);
            }
            $template["rounds"] = ArcheryEventEliminationSchedule::makeTemplateTeam($lis_team, $elimination_member_count);
        }
        $template["updated"] = $updated;
        $template["elimination_group_id"] = $elimination_id;
        return $template;
    }

    public static function getViewDataGraph4($data_graph, $first_loop = 2)
    {
        for ($a = 0; $a <= $first_loop - 1; $a++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['status'] != 'bye') {
                    $round1[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['name'];
                    $round1result[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['result'];
                    $round1position[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['potition'];
                    $round1status[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['status'];
                } else {
                    $round1result[] = '-';
                    $round1[] = 'bye';
                    $round1position[] = '-';
                    $round1status[] = 'wait';
                }
            }
        }

        for ($i = 0; $i < 1; $i++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][1]['seeds'][0]['teams'][$i]['status'] != 'bye') {
                    $round2[] = $data_graph['rounds'][1]['seeds'][0]['teams'][$i]['name'];
                    $round2result[] = $data_graph['rounds'][1]['seeds'][0]['teams'][$i]['result'];
                    $round2position[] = $data_graph['rounds'][1]['seeds'][0]['teams'][$i]['potition'];
                    $round2status[] = $data_graph['rounds'][1]['seeds'][0]['teams'][$i]['status'];
                } else {
                    $round2result[] = '-';
                    $round2[] = 'bye';
                    $round2position[] = '-';
                    $round2status[] = 'wait';
                }
            }
        }

        for ($i = 0; $i < 1; $i++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][2]['seeds'][0]['teams'][$i]['status'] != 'bye') {
                    $round3[] = $data_graph['rounds'][2]['seeds'][0]['teams'][$i]['name'];
                    $round3result[] = $data_graph['rounds'][2]['seeds'][0]['teams'][$i]['result'];
                    $round3position[] = $data_graph['rounds'][2]['seeds'][0]['teams'][$i]['potition'];
                    $round3status[] = $data_graph['rounds'][2]['seeds'][0]['teams'][$i]['status'];
                } else {
                    $round3result[] = '-';
                    $round3[] = 'bye';
                    $round3position[] = '-';
                    $round3status[] = 'wait';
                }
            }
        }

        return array(
            '$round1' => $round1,
            '$round2' => $round2,
            '$round3' => $round3,
            '$round1result' => $round1result,
            '$round2result' => $round2result,
            '$round3result' => $round3result,
            '$round1position' => $round1position,
            '$round2position' => $round2position,
            '$round3position' => $round3position,
            '$round1status' => $round1status,
            '$round2status' => $round2status,
            '$round3status' => $round3status
        );
    }

    public static function getViewDataGraph8($data_graph, $first_loop = 4, $second_loop = 2, $third_loop = 1)
    {
        for ($a = 0; $a <= $first_loop - 1; $a++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['status'] != 'bye') {

                    $round1[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['name'];
                    $round1result[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['result'];
                    $round1position[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['potition'];
                    $round1status[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['status'];
                } else {
                    $round1result[] = '-';
                    $round1[] = 'bye';
                    $round1position[] = '-';
                    $round1status[] = 'wait';
                }
            }
        }


        for ($a = 0; $a <= $second_loop - 1; $a++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['status'] != 'bye') {

                    $round2[] = $data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['name'];
                    $round2result[] = $data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['result'];
                    $round2position[] = $data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['potition'];
                    $round2status[] = $data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['status'];
                } else {
                    $round2result[] = '-';
                    $round2[] = 'bye';
                    $round2position[] = '-';
                    $round2status[] = 'wait';
                }
            }
        }

        for ($a = 0; $a <= $third_loop - 1; $a++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['status'] != 'bye') {

                    $round3[] = $data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['name'];
                    $round3result[] = $data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['result'];
                    $round3position[] = $data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['potition'];
                    $round3status[] = $data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['status'];
                } else {
                    $round3result[] = '-';
                    $round3[] = 'bye';
                    $round3position[] = '-';
                    $round3status[] = 'wait';
                }
            }
        }

        for ($i = 0; $i < 1; $i++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][3]['seeds'][0]['teams'][$i]['status'] != 'bye') {
                    $round4[] = $data_graph['rounds'][3]['seeds'][0]['teams'][$i]['name'];
                    $round4result[] = $data_graph['rounds'][3]['seeds'][0]['teams'][$i]['result'];
                    $round4position[] = $data_graph['rounds'][3]['seeds'][0]['teams'][$i]['potition'];
                    $round4status[] = $data_graph['rounds'][3]['seeds'][0]['teams'][$i]['status'];
                } else {
                    $round4result[] = '-';
                    $round4[] = 'bye';
                    $round4position[] = '-';
                    $round4status[] = 'wait';
                }
            }
        }

        return array('$round1' => $round1, '$round1result' => $round1result, '$round2result' => $round2result, '$round3result' => $round3result, '$round4result' => $round4result, '$round2' => $round2, '$round3' => $round3, '$round4' => $round4, '$round1position' => $round1position, '$round2position' => $round2position, '$round3position' => $round3position, '$round4position' => $round4position, '$round1status' => $round1status, '$round2status' => $round2status, '$round3status' => $round3status, '$round4status' => $round4status);
    }

    public static function getViewDataGraph8_reportDos($data_graph, $first_loop = 4, $second_loop = 2, $third_loop = 1)
    {
        for ($a = 0; $a <= $first_loop - 1; $a++) {
            for ($i = 0; $i <= 1; $i++) {
                if (array_key_exists('status', $data_graph)) {
                    if ($data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['status'] != 'bye') {

                        $round1[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['name'];
                        $round1result[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['result'];
                        $round1position[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['potition'];
                        $round1status[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['status'];
                    } else {
                        $round1result[] = '-';
                        $round1[] = 'bye';
                        $round1position[] = '-';
                        $round1status[] = 'wait';
                    }
                } else {
                    if (array_key_exists('win', $data_graph)) {
                        if ($data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['win'] == 1) {
                            $round1[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['name'];
                            $round1result[] = '-';
                            $round1position[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['potition'];
                            $round1status[] = 'win';
                        } else {
                            $round1result[] = '-';
                            $round1[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['name'];
                            $round1position[] = '-';
                            $round1status[] = 'wait';
                        }
                    } else {
                        $round1result[] = '-';
                        $round1[] = 'bye';
                        $round1position[] = '-';
                        $round1status[] = 'wait';
                    }
                }
            }
        }


        for ($a = 0; $a <= $second_loop - 1; $a++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['status'] != 'bye') {
                    $round2[] = array_key_exists('name', $data_graph) ? $data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['name'] : '-';
                    $round2result[] = array_key_exists('result', $data_graph) ? $data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['result'] : '-';
                    $round2position[] = array_key_exists('potition', $data_graph) ? $data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['potition'] : "-";
                    $round2status[] = $data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['status'];
                } else {
                    $round2result[] = '-';
                    $round2[] = 'bye';
                    $round2position[] = '-';
                    $round2status[] = 'wait';
                }
            }
        }

        for ($a = 0; $a <= $third_loop - 1; $a++) {
            for ($i = 0; $i <= 1; $i++) {
                if (array_key_exists('status', $data_graph)) {
                    if ($data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['status'] != 'bye') {

                        $round3[] = $data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['name'];
                        $round3result[] = $data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['result'];
                        $round3position[] = $data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['potition'];
                        $round3status[] = $data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['status'];
                    } else {
                        $round3result[] = '-';
                        $round3[] = 'bye';
                        $round3position[] = '-';
                        $round3status[] = 'wait';
                    }
                } else {
                    $round3result[] = '-';
                    $round3[] = 'bye';
                    $round3position[] = '-';
                    $round3status[] = 'wait';
                }
            }
        }

        for ($i = 0; $i < 1; $i++) {
            for ($i = 0; $i <= 1; $i++) {
                if (array_key_exists('status', $data_graph)) {
                    if ($data_graph['rounds'][3]['seeds'][0]['teams'][$i]['status'] != 'bye') {
                        $round4[] = $data_graph['rounds'][3]['seeds'][0]['teams'][$i]['name'];
                        $round4result[] = $data_graph['rounds'][3]['seeds'][0]['teams'][$i]['result'];
                        $round4position[] = $data_graph['rounds'][3]['seeds'][0]['teams'][$i]['potition'];
                        $round4status[] = $data_graph['rounds'][3]['seeds'][0]['teams'][$i]['status'];
                    } else {
                        $round4result[] = '-';
                        $round4[] = 'bye';
                        $round4position[] = '-';
                        $round4status[] = 'wait';
                    }
                } else {
                    $round4result[] = '-';
                    $round4[] = 'bye';
                    $round4position[] = '-';
                    $round4status[] = 'wait';
                }
            }
        }

        return array('$round1' => $round1, '$round1result' => $round1result, '$round2result' => $round2result, '$round3result' => $round3result, '$round4result' => $round4result, '$round2' => $round2, '$round3' => $round3, '$round4' => $round4, '$round1position' => $round1position, '$round2position' => $round2position, '$round3position' => $round3position, '$round4position' => $round4position, '$round1status' => $round1status, '$round2status' => $round2status, '$round3status' => $round3status, '$round4status' => $round4status);
    }

    public static function getViewDataGraph16($data_graph, $first_loop = 8, $second_loop = 4, $third_loop = 2)
    {
        for ($a = 0; $a <= 7; $a++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['status'] != 'bye') {
                    $round1[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['name'];
                    $round1result[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['result'];
                    $round1status[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['status'];
                } else {
                    $round1result[] = '-';
                    $round1[] = 'bye';
                    $round1status[] = 'wait';
                }
            }
        }


        for ($a = 0; $a <= 3; $a++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['status'] != 'bye') {

                    $round2[] = $data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['name'];
                    $round2result[] = $data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['result'];
                    $round2status[] = $data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['status'];
                } else {
                    $round2result[] = '-';
                    $round2[] = 'bye';
                    $round2status[] = 'wait';
                }
            }
        }

        for ($a = 0; $a <= 1; $a++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['status'] != 'bye') {

                    $round3[] = $data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['name'];
                    $round3result[] = $data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['result'];
                    $round3status[] = $data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['status'];
                } else {
                    $round3result[] = '-';
                    $round3[] = 'bye';
                    $round3status[] = 'wait';
                }
            }
        }

        for ($i = 0; $i <= 1; $i++) {
            if ($data_graph['rounds'][3]['seeds'][0]['teams'][$i]['status'] != 'bye') {
                $round4[] = $data_graph['rounds'][3]['seeds'][0]['teams'][$i]['name'];
                $round4result[] = $data_graph['rounds'][3]['seeds'][0]['teams'][$i]['result'];
                $round4status[] = $data_graph['rounds'][3]['seeds'][0]['teams'][$i]['status'];
            } else {
                $round4result[] = '-';
                $round4[] = 'bye';
                $round4status[] = 'wait';
            }
            if ($data_graph['rounds'][4]['seeds'][0]['teams'][$i]['status'] != 'bye') {
                $round5[] = $data_graph['rounds'][4]['seeds'][0]['teams'][$i]['name'];
                $round5result[] = $data_graph['rounds'][4]['seeds'][0]['teams'][$i]['result'];
                $round5status[] = $data_graph['rounds'][4]['seeds'][0]['teams'][$i]['status'];
            } else {
                $round5result[] = '-';
                $round5[] = 'bye';
                $round5status[] = 'wait';
            }
        }

        return array(
            '$round1' => $round1,
            '$round2' => $round2,
            '$round3' => $round3,
            '$round4' => $round4,
            '$round5' => $round5,
            '$round1result' => $round1result,
            '$round2result' => $round2result,
            '$round3result' => $round3result,
            '$round4result' => $round4result,
            '$round5result' => $round5result,
            '$round1status' => $round1status,
            '$round2status' => $round2status,
            '$round3status' => $round3status,
            '$round4status' => $round4status,
            '$round5status' => $round5status
        );
    }

    public static function renderPageGraph16($view_path, $data, $title = null)
    {
        return view($view_path, [
            'title' => $title,
            'round1member1' => $data['$round1'][0],
            'round1member2' => $data['$round1'][1],
            'round1member3' => $data['$round1'][2],
            'round1member4' => $data['$round1'][3],
            'round1member5' => $data['$round1'][4],
            'round1member6' => $data['$round1'][5],
            'round1member7' => $data['$round1'][6],
            'round1member8' => $data['$round1'][7],
            'round1member9' => $data['$round1'][8],
            'round1member10' => $data['$round1'][9],
            'round1member11' => $data['$round1'][10],
            'round1member12' => $data['$round1'][11],
            'round1member13' => $data['$round1'][12],
            'round1member14' => $data['$round1'][13],
            'round1member15' => $data['$round1'][14],
            'round1member16' => $data['$round1'][15],
            'round2member1' => $data['$round2'][0],
            'round2member2' => $data['$round2'][1],
            'round2member3' => $data['$round2'][2],
            'round2member4' => $data['$round2'][3],
            'round2member5' => $data['$round2'][4],
            'round2member6' => $data['$round2'][5],
            'round2member7' => $data['$round2'][6],
            'round2member8' => $data['$round2'][7],
            'round3member1' => $data['$round3'][0],
            'round3member2' => $data['$round3'][1],
            'round3member3' => $data['$round3'][2],
            'round3member4' => $data['$round3'][3],
            'round4member1' => $data['$round4'][0],
            'round4member2' => $data['$round4'][1],
            'round5member1' => $data['$round4'][0],
            'round5member2' => $data['$round4'][1],
            'round1member1result' => $data['$round1result'][0],
            'round1member2result' => $data['$round1result'][1],
            'round1member3result' => $data['$round1result'][2],
            'round1member4result' => $data['$round1result'][3],
            'round1member5result' => $data['$round1result'][4],
            'round1member6result' => $data['$round1result'][5],
            'round1member7result' => $data['$round1result'][6],
            'round1member8result' => $data['$round1result'][7],
            'round1member9result' => $data['$round1result'][8],
            'round1member10result' => $data['$round1result'][9],
            'round1member11result' => $data['$round1result'][10],
            'round1member12result' => $data['$round1result'][11],
            'round1member13result' => $data['$round1result'][12],
            'round1member14result' => $data['$round1result'][13],
            'round1member15result' => $data['$round1result'][14],
            'round1member16result' => $data['$round1result'][15],
            'round2member1result' => $data['$round2result'][0],
            'round2member2result' => $data['$round2result'][1],
            'round2member3result' => $data['$round2result'][2],
            'round2member4result' => $data['$round2result'][3],
            'round2member5result' => $data['$round2result'][4],
            'round2member6result' => $data['$round2result'][5],
            'round2member7result' => $data['$round2result'][6],
            'round2member8result' => $data['$round2result'][7],
            'round3member1result' => $data['$round3result'][0],
            'round3member2result' => $data['$round3result'][1],
            'round3member3result' => $data['$round3result'][2],
            'round3member4result' => $data['$round3result'][3],
            'round4member1result' => $data['$round4result'][0],
            'round4member2result' => $data['$round4result'][1],
            'round5member1result' => $data['$round5result'][0],
            'round5member2result' => $data['$round5result'][1],

            'round1member1status' => $data['$round1status'][0],
            'round1member2status' => $data['$round1status'][1],
            'round1member3status' => $data['$round1status'][2],
            'round1member4status' => $data['$round1status'][3],
            'round1member5status' => $data['$round1status'][4],
            'round1member6status' => $data['$round1status'][5],
            'round1member7status' => $data['$round1status'][6],
            'round1member8status' => $data['$round1status'][7],
            'round1member9status' => $data['$round1status'][8],
            'round1member10status' => $data['$round1status'][9],
            'round1member11status' => $data['$round1status'][10],
            'round1member12status' => $data['$round1status'][11],
            'round1member13status' => $data['$round1status'][12],
            'round1member14status' => $data['$round1status'][13],
            'round1member15status' => $data['$round1status'][14],
            'round1member16status' => $data['$round1status'][15],
            'round2member1status' => $data['$round2status'][0],
            'round2member2status' => $data['$round2status'][1],
            'round2member3status' => $data['$round2status'][2],
            'round2member4status' => $data['$round2status'][3],
            'round2member5status' => $data['$round2status'][4],
            'round2member6status' => $data['$round2status'][5],
            'round2member8status' => $data['$round2status'][7],
            'round3member1status' => $data['$round3status'][0],
            'round3member2status' => $data['$round3status'][1],
            'round3member3status' => $data['$round3status'][2],
            'round3member4status' => $data['$round3status'][3],
            'round4member1status' => $data['$round4status'][0],
            'round4member2status' => $data['$round4status'][1],
            'round5member1status' => $data['$round5status'][0],
            'round5member2status' => $data['$round5status'][1],
            'round2member7status' => $data['$round2status'][6],

        ]);
    }

    public static function renderPageGraph8($view_path, $data, $title = null)
    {
        return view($view_path, [
            'title' => $title,
            'round1member1' => $data['$round1'][0],
            'round1member2' => $data['$round1'][1],
            'round1member3' => $data['$round1'][2],
            'round1member4' => $data['$round1'][3],
            'round1member5' => $data['$round1'][4],
            'round1member6' => $data['$round1'][5],
            'round1member7' => $data['$round1'][6],
            'round1member8' => $data['$round1'][7],
            'round2member1' => $data['$round2'][0],
            'round2member2' => $data['$round2'][1],
            'round2member3' => $data['$round2'][2],
            'round2member4' => $data['$round2'][3],
            'round3member1' => $data['$round3'][0],
            'round3member2' => $data['$round3'][1],
            'round4member1' => $data['$round4'][0],
            'round4member2' => $data['$round4'][1],

            'round1member1result' => $data['$round1result'][0],
            'round1member2result' => $data['$round1result'][1],
            'round1member3result' => $data['$round1result'][2],
            'round1member4result' => $data['$round1result'][3],
            'round1member5result' => $data['$round1result'][4],
            'round1member6result' => $data['$round1result'][5],
            'round1member7result' => $data['$round1result'][6],
            'round1member8result' => $data['$round1result'][7],
            'round2member1result' => $data['$round2result'][0],
            'round2member2result' => $data['$round2result'][1],
            'round2member3result' => $data['$round2result'][2],
            'round2member4result' => $data['$round2result'][3],
            'round3member1result' => $data['$round3result'][0],
            'round3member2result' => $data['$round3result'][1],
            'round4member1result' => $data['$round4result'][0],
            'round4member2result' => $data['$round4result'][1],

            'round1member1position' => $data['$round1position'][0],
            'round1member2position' => $data['$round1position'][1],
            'round1member3position' => $data['$round1position'][2],
            'round1member4position' => $data['$round1position'][3],
            'round1member5position' => $data['$round1position'][4],
            'round1member6position' => $data['$round1position'][5],
            'round1member7position' => $data['$round1position'][6],
            'round1member8position' => $data['$round1position'][7],
            'round2member1position' => $data['$round2position'][0],
            'round2member2position' => $data['$round2position'][1],
            'round2member3position' => $data['$round2position'][2],
            'round2member4position' => $data['$round2position'][3],
            'round3member1position' => $data['$round3position'][0],
            'round3member2position' => $data['$round3position'][1],
            'round4member1position' => $data['$round4position'][0],
            'round4member2position' => $data['$round4position'][1],

            'round1member1status' => $data['$round1status'][0],
            'round1member2status' => $data['$round1status'][1],
            'round1member3status' => $data['$round1status'][2],
            'round1member4status' => $data['$round1status'][3],
            'round1member5status' => $data['$round1status'][4],
            'round1member6status' => $data['$round1status'][5],
            'round1member7status' => $data['$round1status'][6],
            'round1member8status' => $data['$round1status'][7],
            'round2member1status' => $data['$round2status'][0],
            'round2member2status' => $data['$round2status'][1],
            'round2member3status' => $data['$round2status'][2],
            'round2member4status' => $data['$round2status'][3],
            'round3member1status' => $data['$round3status'][0],
            'round3member2status' => $data['$round3status'][1],
            'round4member1status' => $data['$round4status'][0],
            'round4member2status' => $data['$round4status'][1],

        ]);
    }

    public static function renderPageGraph16_reportEvent($view_path, $data, $report, $data_report, $logo_event, $logo_archery, $competition)
    {
        return view($view_path, [
            'round1member1' => $data['$round1'][0],
            'round1member2' => $data['$round1'][1],
            'round1member3' => $data['$round1'][2],
            'round1member4' => $data['$round1'][3],
            'round1member5' => $data['$round1'][4],
            'round1member6' => $data['$round1'][5],
            'round1member7' => $data['$round1'][6],
            'round1member8' => $data['$round1'][7],
            'round1member9' => $data['$round1'][8],
            'round1member10' => $data['$round1'][9],
            'round1member11' => $data['$round1'][10],
            'round1member12' => $data['$round1'][11],
            'round1member13' => $data['$round1'][12],
            'round1member14' => $data['$round1'][13],
            'round1member15' => $data['$round1'][14],
            'round1member16' => $data['$round1'][15],
            'round2member1' => $data['$round2'][0],
            'round2member2' => $data['$round2'][1],
            'round2member3' => $data['$round2'][2],
            'round2member4' => $data['$round2'][3],
            'round2member5' => $data['$round2'][4],
            'round2member6' => $data['$round2'][5],
            'round2member7' => $data['$round2'][6],
            'round2member8' => $data['$round2'][7],
            'round3member1' => $data['$round3'][0],
            'round3member2' => $data['$round3'][1],
            'round3member3' => $data['$round3'][2],
            'round3member4' => $data['$round3'][3],
            'round4member1' => $data['$round4'][0],
            'round4member2' => $data['$round4'][1],
            'round5member1' => $data['$round4'][0],
            'round5member2' => $data['$round4'][1],
            'round1member1result' => $data['$round1result'][0],
            'round1member2result' => $data['$round1result'][1],
            'round1member3result' => $data['$round1result'][2],
            'round1member4result' => $data['$round1result'][3],
            'round1member5result' => $data['$round1result'][4],
            'round1member6result' => $data['$round1result'][5],
            'round1member7result' => $data['$round1result'][6],
            'round1member8result' => $data['$round1result'][7],
            'round1member9result' => $data['$round1result'][8],
            'round1member10result' => $data['$round1result'][9],
            'round1member11result' => $data['$round1result'][10],
            'round1member12result' => $data['$round1result'][11],
            'round1member13result' => $data['$round1result'][12],
            'round1member14result' => $data['$round1result'][13],
            'round1member15result' => $data['$round1result'][14],
            'round1member16result' => $data['$round1result'][15],
            'round2member1result' => $data['$round2result'][0],
            'round2member2result' => $data['$round2result'][1],
            'round2member3result' => $data['$round2result'][2],
            'round2member4result' => $data['$round2result'][3],
            'round2member5result' => $data['$round2result'][4],
            'round2member6result' => $data['$round2result'][5],
            'round2member7result' => $data['$round2result'][6],
            'round2member8result' => $data['$round2result'][7],
            'round3member1result' => $data['$round3result'][0],
            'round3member2result' => $data['$round3result'][1],
            'round3member3result' => $data['$round3result'][2],
            'round3member4result' => $data['$round3result'][3],
            'round4member1result' => $data['$round4result'][0],
            'round4member2result' => $data['$round4result'][1],
            'round5member1result' => $data['$round5result'][0],
            'round5member2result' => $data['$round5result'][1],

            'round1member1status' => $data['$round1status'][0],
            'round1member2status' => $data['$round1status'][1],
            'round1member3status' => $data['$round1status'][2],
            'round1member4status' => $data['$round1status'][3],
            'round1member5status' => $data['$round1status'][4],
            'round1member6status' => $data['$round1status'][5],
            'round1member7status' => $data['$round1status'][6],
            'round1member8status' => $data['$round1status'][7],
            'round1member9status' => $data['$round1status'][8],
            'round1member10status' => $data['$round1status'][9],
            'round1member11status' => $data['$round1status'][10],
            'round1member12status' => $data['$round1status'][11],
            'round1member13status' => $data['$round1status'][12],
            'round1member14status' => $data['$round1status'][13],
            'round1member15status' => $data['$round1status'][14],
            'round1member16status' => $data['$round1status'][15],
            'round2member1status' => $data['$round2status'][0],
            'round2member2status' => $data['$round2status'][1],
            'round2member3status' => $data['$round2status'][2],
            'round2member4status' => $data['$round2status'][3],
            'round2member5status' => $data['$round2status'][4],
            'round2member6status' => $data['$round2status'][5],
            'round2member7status' => $data['$round2status'][6],
            'round2member8status' => $data['$round2status'][7],
            'round3member1status' => $data['$round3status'][0],
            'round3member2status' => $data['$round3status'][1],
            'round3member3status' => $data['$round3status'][2],
            'round3member4status' => $data['$round3status'][3],
            'round4member1status' => $data['$round4status'][0],
            'round4member2status' => $data['$round4status'][1],
            'round5member1status' => $data['$round5status'][0],
            'round5member2status' => $data['$round5status'][1],

            'report' => $report,
            'category' => $data_report[0][0]['category'],
            'logo_event' => $logo_event,
            'logo_archery' => $logo_archery,
            'competition' => $competition->competition_category,

        ]);
    }

    public static function renderPageGraph8_reportEvent($view_path, $data, $report, $data_report, $logo_event, $logo_archery, $competition, $event_name_report, $event_location_report, $event_date_report)
    {
        return view($view_path, [
            'round1member1' => $data['$round1'][0],
            'round1member2' => $data['$round1'][1],
            'round1member3' => $data['$round1'][2],
            'round1member4' => $data['$round1'][3],
            'round1member5' => $data['$round1'][4],
            'round1member6' => $data['$round1'][5],
            'round1member7' => $data['$round1'][6],
            'round1member8' => $data['$round1'][7],
            'round2member1' => $data['$round2'][0],
            'round2member2' => $data['$round2'][1],
            'round2member3' => $data['$round2'][2],
            'round2member4' => $data['$round2'][3],
            'round3member1' => $data['$round3'][0],
            'round3member2' => $data['$round3'][1],
            'round4member1' => $data['$round4'][0],
            'round4member2' => $data['$round4'][1],

            'round1member1result' => $data['$round1result'][0],
            'round1member2result' => $data['$round1result'][1],
            'round1member3result' => $data['$round1result'][2],
            'round1member4result' => $data['$round1result'][3],
            'round1member5result' => $data['$round1result'][4],
            'round1member6result' => $data['$round1result'][5],
            'round1member7result' => $data['$round1result'][6],
            'round1member8result' => $data['$round1result'][7],
            'round2member1result' => $data['$round2result'][0],
            'round2member2result' => $data['$round2result'][1],
            'round2member3result' => $data['$round2result'][2],
            'round2member4result' => $data['$round2result'][3],
            'round3member1result' => $data['$round3result'][0],
            'round3member2result' => $data['$round3result'][1],
            'round4member1result' => $data['$round4result'][0],
            'round4member2result' => $data['$round4result'][1],

            'round1member1position' => $data['$round1position'][0],
            'round1member2position' => $data['$round1position'][1],
            'round1member3position' => $data['$round1position'][2],
            'round1member4position' => $data['$round1position'][3],
            'round1member5position' => $data['$round1position'][4],
            'round1member6position' => $data['$round1position'][5],
            'round1member7position' => $data['$round1position'][6],
            'round1member8position' => $data['$round1position'][7],
            'round2member1position' => $data['$round2position'][0],
            'round2member2position' => $data['$round2position'][1],
            'round2member3position' => $data['$round2position'][2],
            'round2member4position' => $data['$round2position'][3],
            'round3member1position' => $data['$round3position'][0],
            'round3member2position' => $data['$round3position'][1],
            'round4member1position' => $data['$round4position'][0],
            'round4member2position' => $data['$round4position'][1],

            'round1member1status' => $data['$round1status'][0],
            'round1member2status' => $data['$round1status'][1],
            'round1member3status' => $data['$round1status'][2],
            'round1member4status' => $data['$round1status'][3],
            'round1member5status' => $data['$round1status'][4],
            'round1member6status' => $data['$round1status'][5],
            'round1member7status' => $data['$round1status'][6],
            'round1member8status' => $data['$round1status'][7],
            'round2member1status' => $data['$round2status'][0],
            'round2member2status' => $data['$round2status'][1],
            'round2member3status' => $data['$round2status'][2],
            'round2member4status' => $data['$round2status'][3],
            'round3member1status' => $data['$round3status'][0],
            'round3member2status' => $data['$round3status'][1],
            'round4member1status' => $data['$round4status'][0],
            'round4member2status' => $data['$round4status'][1],

            'report' => $report,
            'category' => $data_report[0][0]['category'],
            'logo_event' => $logo_event,
            'logo_archery' => $logo_archery,
            'competition' => $competition->competition_category,
            'event_name_report' => $event_name_report,
            'event_location_report' => $event_location_report,
            "event_date_report" => $event_date_report
        ]);
    }

    public static function renderPageGraph4_reportEvent($view_path, $data, $report, $data_report, $logo_event, $logo_archery, $competition, $event_name_report, $event_location_report, $event_date_report)
    {
        return view($view_path, [
            'round1member1' => $data['$round1'][0],
            'round1member2' => $data['$round1'][1],
            'round1member3' => $data['$round1'][2],
            'round1member4' => $data['$round1'][3],

            'round2member1' => $data['$round2'][0],
            'round2member2' => $data['$round2'][1],

            'round3member1' => $data['$round3'][0],
            'round3member2' => $data['$round3'][1],


            'round1member1result' => $data['$round1result'][0],
            'round1member2result' => $data['$round1result'][1],
            'round1member3result' => $data['$round1result'][2],
            'round1member4result' => $data['$round1result'][3],

            'round2member1result' => $data['$round2result'][0],
            'round2member2result' => $data['$round2result'][1],

            'round3member1result' => $data['$round3result'][0],
            'round3member2result' => $data['$round3result'][1],


            'round1member1position' => $data['$round1position'][0],
            'round1member2position' => $data['$round1position'][1],
            'round1member3position' => $data['$round1position'][2],
            'round1member4position' => $data['$round1position'][3],

            'round2member1position' => $data['$round2position'][0],
            'round2member2position' => $data['$round2position'][1],

            'round3member1position' => $data['$round3position'][0],
            'round3member2position' => $data['$round3position'][1],


            'round1member1status' => $data['$round1status'][0],
            'round1member2status' => $data['$round1status'][1],
            'round1member3status' => $data['$round1status'][2],
            'round1member4status' => $data['$round1status'][3],

            'round2member1status' => $data['$round2status'][0],
            'round2member2status' => $data['$round2status'][1],

            'round3member1status' => $data['$round3status'][0],
            'round3member2status' => $data['$round3status'][1],


            'report' => $report,
            'category' => $data_report[0][0]['category'],
            'logo_event' => $logo_event,
            'logo_archery' => $logo_archery,
            'competition' => $competition->competition_category,
            'event_name_report' => $event_name_report,
            'event_location_report' => $event_location_report,
            "event_date_report" => $event_date_report
        ]);
    }
}
