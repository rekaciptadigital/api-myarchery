<?php

namespace App\Libraries;
use App\Models\ArcheryScoring;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventEliminationSchedule;

class EliminationFormatPDF
{
    public static function getDataGraph($event_category_id)
    {
        $elimination = ArcheryEventElimination::where("event_category_id", $event_category_id)->first();
        $elimination_id = 0;
        if ($elimination) {
            $match_type = $elimination->elimination_type;
            $elimination_member_count = $elimination->count_participant;
            $gender = $elimination->gender;
            $elimination_id = $elimination->id;
        }

        $category = ArcheryEventCategoryDetail::find($event_category_id);
        $score_type = 1; // 1 for type qualification
        $session = [];
        for ($i = 0; $i < $category->session_in_qualification; $i++) {
            $session[] = $i + 1;
        }

        $fix_members = ArcheryEventEliminationMatch::select(
            "archery_event_elimination_members.position_qualification",
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
                    $members[$value->round][$value->match]["teams"][] = array(
                        "id" => $value->member_id,
                        "name" => $value->name,
                        "gender" => $value->gender,
                        "club" => $value->club,
                        "potition" => $value->position_qualification,
                        "win" => $value->win,
                        "result" => $value->result,
                        "status" => $value->win == 1 ? "win" : "wait"
                    );
                } else {
                    $members[$value->round][$value->match]["teams"][] = ["status" => "bye"];
                }
            }

            $fix_members = $members;
            $updated = false;
            $template["rounds"] = ArcheryEventEliminationSchedule::getTemplate($fix_members, $elimination_member_count);
        } else {
            $qualification_rank = ArcheryScoring::getScoringRankByCategoryId($event_category_id, $score_type, $session);
            $template["rounds"] = ArcheryEventEliminationSchedule::makeTemplate($qualification_rank, $elimination_member_count);
        }
        // $template["rounds"] = ArcheryEventEliminationSchedule::makeTemplate2($qualification_rank, $elimination_member_count, $match_type, $event_category_id, $gender, $fix_members);
        $template["updated"] = $updated;
        $template["elimination_id"] = $elimination_id;
        return $template;
    }

    public static function getViewDataGraph8($data_graph, $first_loop = 4, $second_loop = 2, $third_loop = 1)
    {
        for ($a = 0; $a <= $first_loop-1; $a++) {
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


        for ($a = 0; $a <= $second_loop-1; $a++) {
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

        for ($a = 0; $a <= $third_loop-1; $a++) {
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

    public static function getViewDataGraph16($data_graph, $first_loop = 8, $second_loop = 4, $third_loop = 2)
    {
        for ($a = 0; $a <= 7; $a++) {
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


        for ($a = 0; $a <= 3; $a++) {
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

        for ($a = 0; $a <= 1; $a++) {
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
            if ($data_graph['rounds'][4]['seeds'][0]['teams'][$i]['status'] != 'bye') {
                $round5[] = $data_graph['rounds'][4]['seeds'][0]['teams'][$i]['name'];
                $round5result[] = $data_graph['rounds'][4]['seeds'][0]['teams'][$i]['result'];
                $round5position[] = $data_graph['rounds'][4]['seeds'][0]['teams'][$i]['potition'];
                $round5status[] = $data_graph['rounds'][4]['seeds'][0]['teams'][$i]['status'];
            } else {
                $round5result[] = '-';
                $round5[] = 'bye';
                $round5position[] = '-';
                $round5status[] = 'wait';
            }
        }

        return array('$round1' => $round1, '$round1result' => $round1result, '$round2result' => $round2result, '$round3result' => $round3result, '$round4result' => $round4result, '$round5result' => $round5result, '$round2' => $round2, '$round3' => $round3, '$round4' => $round4, '$round5' => $round5, '$round1position' => $round1position, '$round2position' => $round2position, '$round3position' => $round3position, '$round4position' => $round4position, '$round5position' => $round5position, '$round1status' => $round1status, '$round2status' => $round2status, '$round3status' => $round3status, '$round4status' => $round4status, '$round5status' => $round5status);
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
            'round1member1result' => $data['$round1result'][0],
            'round5member2' => $data['$round4'][1],
            'round1member3result' => $data['$round1result'][2],
            'round1member4result' => $data['$round1result'][3],
            'round1member5result' => $data['$round1result'][4],
            'round1member6result' => $data['$round1result'][5],
            'round1member8' => $data['$round1'][7],
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
            
            'round1member1position' => $data['$round1position'][0],
            'round1member2position' => $data['$round1position'][1],
            'round1member3position' => $data['$round1position'][2],
            'round1member4position' => $data['$round1position'][3],
            'round1member5position' => $data['$round1position'][4],
            'round1member6position' => $data['$round1position'][5],
            'round1member7position' => $data['$round1position'][6],
            'round1member8position' => $data['$round1position'][7],
            'round1member9position' => $data['$round1position'][8],
            'round1member10position' => $data['$round1position'][9],
            'round1member11position' => $data['$round1position'][10],
            'round1member12position' => $data['$round1position'][11],
            'round1member13position' => $data['$round1position'][12],
            'round1member14position' => $data['$round1position'][13],
            'round1member15position' => $data['$round1position'][14],
            'round1member16position' => $data['$round1position'][15],
            'round2member1position' => $data['$round2position'][0],
            'round2member2position' => $data['$round2position'][1],
            'round2member3position' => $data['$round2position'][2],
            'round2member4position' => $data['$round2position'][3],
            'round2member5position' => $data['$round2position'][4],
            'round2member6position' => $data['$round2position'][5],
            'round2member7position' => $data['$round2position'][6],
            'round2member8position' => $data['$round2position'][7],
            'round3member1position' => $data['$round3position'][0],
            'round3member2position' => $data['$round3position'][1],
            'round3member3position' => $data['$round3position'][2],
            'round3member4position' => $data['$round3position'][3],
            'round4member1position' => $data['$round4position'][0],
            'round4member2position' => $data['$round4position'][1],
            'round5member1position' => $data['$round5position'][0],
            'round5member2position' => $data['$round5position'][1],
            
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
            'round1member2result' => $data['$round1result'][1],
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

            'round1member1position' => $data['$round1position'][0],
            'round1member2position' => $data['$round1position'][1],
            'round1member3position' => $data['$round1position'][2],
            'round1member4position' => $data['$round1position'][3],
            'round1member5position' => $data['$round1position'][4],
            'round1member6position' => $data['$round1position'][5],
            'round1member7position' => $data['$round1position'][6],
            'round1member8position' => $data['$round1position'][7],
            'round1member9position' => $data['$round1position'][8],
            'round1member10position' => $data['$round1position'][9],
            'round1member11position' => $data['$round1position'][10],
            'round1member12position' => $data['$round1position'][11],
            'round1member13position' => $data['$round1position'][12],
            'round1member14position' => $data['$round1position'][13],
            'round1member15position' => $data['$round1position'][14],
            'round1member16position' => $data['$round1position'][15],
            'round2member1position' => $data['$round2position'][0],
            'round2member2position' => $data['$round2position'][1],
            'round2member3position' => $data['$round2position'][2],
            'round2member4position' => $data['$round2position'][3],
            'round2member5position' => $data['$round2position'][4],
            'round2member6position' => $data['$round2position'][5],
            'round2member7position' => $data['$round2position'][6],
            'round2member8position' => $data['$round2position'][7],
            'round3member1position' => $data['$round3position'][0],
            'round3member2position' => $data['$round3position'][1],
            'round3member3position' => $data['$round3position'][2],
            'round3member4position' => $data['$round3position'][3],
            'round4member1position' => $data['$round4position'][0],
            'round4member2position' => $data['$round4position'][1],
            'round5member1position' => $data['$round5position'][0],
            'round5member2position' => $data['$round5position'][1],

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

    public static function renderPageGraph8_reportEvent($view_path, $data, $report, $data_report, $logo_event, $logo_archery, $competition)
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

        ]);
    }
}