<?php

namespace App\Libraries;

use App\Models\ArcheryScoring;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventEliminationSchedule;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventEliminationGroupMatch;
use App\Models\ArcheryScoringEliminationGroup;
use App\Models\ArcheryEventEliminationGroupMemberTeam;


class EliminationFormatPDFV2
{
    public static function getViewDataGraphTeamOfBigFour($data_graph, $first_loop = 2)
    {
        for ($a = 0; $a <= $first_loop - 1; $a++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['status'] != 'bye') {

                    $round1[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['team_name'] ?? 'bye';
                    $round1result[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['result'] ?? '-';
                    $round1status[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['status'];
                } else {
                    $round1result[] = '-';
                    $round1[] = 'bye';
                    $round1status[] = 'wait';
                }
            }
        }

        for ($i = 0; $i < 1; $i++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][1]['seeds'][0]['teams'][$i]['status'] != 'bye') {
                    $round2[] = $data_graph['rounds'][1]['seeds'][0]['teams'][$i]['team_name'] ?? 'bye';
                    $round2result[] = $data_graph['rounds'][1]['seeds'][0]['teams'][$i]['result'] ?? '-';
                    $round2status[] = $data_graph['rounds'][1]['seeds'][0]['teams'][$i]['status'];
                } else {
                    $round2result[] = '-';
                    $round2[] = 'bye';
                    $round2status[] = 'wait';
                }
            }
        }

        for ($i = 0; $i < 1; $i++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][2]['seeds'][0]['teams'][$i]['status'] != 'bye') {
                    $round3[] = $data_graph['rounds'][2]['seeds'][0]['teams'][$i]['team_name'] ?? 'bye';
                    $round3result[] = $data_graph['rounds'][2]['seeds'][0]['teams'][$i]['result'] ?? '-';
                    $round3status[] = $data_graph['rounds'][2]['seeds'][0]['teams'][$i]['status'];
                } else {
                    $round3result[] = '-';
                    $round3[] = 'bye';
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
            '$round1status' => $round1status,
            '$round2status' => $round2status,
            '$round3status' => $round3status,
        );
    }

    public static function getViewDataGraphTeamOfBigEight($data_graph, $first_loop = 4, $second_loop = 2)
    {
        // round 8 besar
        for ($a = 0; $a <= $first_loop - 1; $a++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['status'] != 'bye') {

                    $round1[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['team_name'] ?? 'bye';
                    $round1result[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['result'] ?? '-';
                    $round1status[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['status'];
                } else {
                    $round1result[] = '-';
                    $round1[] = 'bye';
                    $round1status[] = 'wait';
                }
            }
        }

        // round 4 besar
        for ($a = 0; $a <= $second_loop - 1; $a++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['status'] != 'bye') {

                    $round2[] = $data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['team_name'] ?? 'bye';
                    $round2result[] = $data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['result'];
                    $round2status[] = $data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['status'];
                } else {
                    $round2result[] = '-';
                    $round2[] = 'bye';
                    $round2status[] = 'wait';
                }
            }
        }

        // round final
        for ($i = 0; $i <= 1; $i++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][2]['seeds'][0]['teams'][$i]['status'] != 'bye') {
                    $round3[] = $data_graph['rounds'][2]['seeds'][0]['teams'][$i]['team_name'] ?? 'bye';
                    $round3result[] = $data_graph['rounds'][2]['seeds'][0]['teams'][$i]['result'] ?? '-';
                    $round3status[] = $data_graph['rounds'][2]['seeds'][0]['teams'][$i]['status'];
                } else {
                    $round3result[] = '-';
                    $round3[] = 'bye';
                    $round3status[] = 'wait';
                }
            }
        }

        // round bronze
        for ($i = 0; $i < 1; $i++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][3]['seeds'][0]['teams'][$i]['status'] != 'bye') {
                    $round4[] = $data_graph['rounds'][3]['seeds'][0]['teams'][$i]['team_name'] ?? 'bye';
                    $round4result[] = $data_graph['rounds'][3]['seeds'][0]['teams'][$i]['result'] ?? '-';
                    $round4status[] = $data_graph['rounds'][3]['seeds'][0]['teams'][$i]['status'];
                } else {
                    $round4result[] = '-';
                    $round4[] = 'bye';
                    $round4status[] = 'wait';
                }
            }
        }

        return array(
            '$round1' => $round1,
            '$round2' => $round2,
            '$round3' => $round3,
            '$round4' => $round4,
            '$round1result' => $round1result,
            '$round2result' => $round2result,
            '$round3result' => $round3result,
            '$round4result' => $round4result,
            '$round1status' => $round1status,
            '$round2status' => $round2status,
            '$round3status' => $round3status,
            '$round4status' => $round4status,
        );
    }

    public static function getViewDataGraphIndividuOfBigSixteen($data_graph, $first_loop = 8, $second_loop = 4, $third_loop = 2)
    {
        // round 1 (16 besar)
        for ($a = 0; $a <= $first_loop - 1; $a++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['status'] != 'bye') {

                    $round1[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['name'] ?? 'bye';
                    // if ($data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['name']) {
                    //     $round2[] = self::substrName($data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['name']);
                    // } else {
                    //     $round2[] = 'bye';
                    // }
                    $round1result[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['result'] ?? '-';
                    $round1status[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['status'];
                } else {
                    $round1result[] = '-';
                    $round1[] = 'bye';
                    $round1status[] = 'wait';
                }
            }
        }

        // round 2 (8 besar)
        for ($a = 0; $a <= $second_loop - 1; $a++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['status'] != 'bye') {

                    $round2[] = $data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['name'] ?? 'bye';
                    // if ($data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['name'] ) {
                    //     $round3[] = self::substrName($data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['name']);
                    // } else {
                    //     $round3[] = 'bye';
                    // }
                    $round2result[] = $data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['result'] ?? '-';
                    $round2status[] = $data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['status'];
                } else {
                    $round2result[] = '-';
                    $round2[] = 'bye';
                    $round2status[] = 'wait';
                }
            }
        }

        // round 3 (4 besar)
        for ($a = 0; $a <= $third_loop - 1; $a++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['status'] != 'bye') {

                    $round3[] = $data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['name'] ?? 'bye';
                    // if ($data_graph['rounds'][3]['seeds'][$a]['teams'][$i]['name']) {
                    //     $round4[] = self::substrName($data_graph['rounds'][3]['seeds'][$a]['teams'][$i]['name']);
                    // } else {
                    //     $round4[] = 'bye';
                    // }
                    $round3result[] = $data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['result'] ?? '-';
                    $round3status[] = $data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['status'];
                } else {
                    $round3result[] = '-';
                    $round3[] = 'bye';
                    $round3status[] = 'wait';
                }
            }
        }

        // round 4 (gold medal)
        for ($i = 0; $i < 1; $i++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][3]['seeds'][0]['teams'][$i]['status'] != 'bye') {

                    $round4[] = $data_graph['rounds'][3]['seeds'][0]['teams'][$i]['name'] ?? 'bye';
                    // if ($data_graph['rounds'][4]['seeds'][0]['teams'][$i]['name']) {
                    //     $round5[] = self::substrName($data_graph['rounds'][4]['seeds'][0]['teams'][$i]['name']);
                    // } else {
                    //     $round5[] = 'bye';
                    // }
                    $round4result[] = $data_graph['rounds'][3]['seeds'][0]['teams'][$i]['result'] ?? '-';
                    $round4status[] = $data_graph['rounds'][3]['seeds'][0]['teams'][$i]['status'];
                } else {
                    $round4result[] = '-';
                    $round4[] = 'bye';
                    $round4status[] = 'wait';
                }
            }
        }

        // round 6 (bronze medal)
        for ($i = 0; $i < 1; $i++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][4]['seeds'][0]['teams'][$i]['status'] != 'bye') {

                    // if ($data_graph['rounds'][5]['seeds'][0]['teams'][$i]['name']) {
                    //     $round6[] = self::substrName($data_graph['rounds'][5]['seeds'][0]['teams'][$i]['name']);
                    // } else {
                    //     $round6[] = 'bye';
                    // }
                    $round5[] = $data_graph['rounds'][4]['seeds'][0]['teams'][$i]['name'] ?? 'bye';
                    $round5result[] = $data_graph['rounds'][4]['seeds'][0]['teams'][$i]['result'] ?? '-';
                    $round5status[] = $data_graph['rounds'][4]['seeds'][0]['teams'][$i]['status'];
                } else {
                    $round5result[] = '-';
                    $round5[] = 'bye';
                    $round5status[] = 'wait';
                }
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

    public static function getViewDataGraphIndividuOfBigTwentyTwo($data_graph, $first_loop = 16, $second_loop = 8, $third_loop = 4, $fourth_loop = 2)
    {
        // round 1 (32 besar)
        for ($a = 0; $a <= $first_loop - 1; $a++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['status'] != 'bye') {

                    $round1[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['name'] ?? 'bye';
                    // if ($data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['name']) {
                    //     $round1[] = self::substrName($data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['name']);
                    // } else {
                    //     $round1[] = 'bye';
                    // }
                    $round1result[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['result'] ?? '-';
                    $round1status[] = $data_graph['rounds'][0]['seeds'][$a]['teams'][$i]['status'];
                } else {
                    $round1result[] = '-';
                    $round1[] = 'bye';
                    $round1status[] = 'wait';
                }
            }
        }

        // round 2 (16 besar)
        for ($a = 0; $a <= $second_loop - 1; $a++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['status'] != 'bye') {

                    $round2[] = $data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['name'] ?? 'bye';
                    // if ($data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['name']) {
                    //     $round2[] = self::substrName($data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['name']);
                    // } else {
                    //     $round2[] = 'bye';
                    // }
                    $round2result[] = $data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['result'] ?? '-';
                    $round2status[] = $data_graph['rounds'][1]['seeds'][$a]['teams'][$i]['status'];
                } else {
                    $round2result[] = '-';
                    $round2[] = 'bye';
                    $round2status[] = 'wait';
                }
            }
        }

        // round 3 (8 besar)
        for ($a = 0; $a <= $third_loop - 1; $a++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['status'] != 'bye') {

                    $round3[] = $data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['name'] ?? 'bye';
                    // if ($data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['name'] ) {
                    //     $round3[] = self::substrName($data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['name']);
                    // } else {
                    //     $round3[] = 'bye';
                    // }
                    $round3result[] = $data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['result'] ?? '-';
                    $round3status[] = $data_graph['rounds'][2]['seeds'][$a]['teams'][$i]['status'];
                } else {
                    $round3result[] = '-';
                    $round3[] = 'bye';
                    $round3status[] = 'wait';
                }
            }
        }

        // round 4 (4 besar)
        for ($a = 0; $a <= $fourth_loop - 1; $a++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][3]['seeds'][$a]['teams'][$i]['status'] != 'bye') {

                    $round4[] = $data_graph['rounds'][3]['seeds'][$a]['teams'][$i]['name'] ?? 'bye';
                    // if ($data_graph['rounds'][3]['seeds'][$a]['teams'][$i]['name']) {
                    //     $round4[] = self::substrName($data_graph['rounds'][3]['seeds'][$a]['teams'][$i]['name']);
                    // } else {
                    //     $round4[] = 'bye';
                    // }
                    $round4result[] = $data_graph['rounds'][3]['seeds'][$a]['teams'][$i]['result'] ?? '-';
                    $round4status[] = $data_graph['rounds'][3]['seeds'][$a]['teams'][$i]['status'];
                } else {
                    $round4result[] = '-';
                    $round4[] = 'bye';
                    $round4status[] = 'wait';
                }
            }
        }

        // round 5 (gold medal)
        for ($i = 0; $i < 1; $i++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][4]['seeds'][0]['teams'][$i]['status'] != 'bye') {

                    $round5[] = $data_graph['rounds'][4]['seeds'][0]['teams'][$i]['name'] ?? 'bye';
                    // if ($data_graph['rounds'][4]['seeds'][0]['teams'][$i]['name']) {
                    //     $round5[] = self::substrName($data_graph['rounds'][4]['seeds'][0]['teams'][$i]['name']);
                    // } else {
                    //     $round5[] = 'bye';
                    // }
                    $round5result[] = $data_graph['rounds'][4]['seeds'][0]['teams'][$i]['result'] ?? '-';
                    $round5status[] = $data_graph['rounds'][4]['seeds'][0]['teams'][$i]['status'];
                } else {
                    $round5result[] = '-';
                    $round5[] = 'bye';
                    $round5status[] = 'wait';
                }
            }
        }

        // round 6 (bronze medal)
        for ($i = 0; $i < 1; $i++) {
            for ($i = 0; $i <= 1; $i++) {
                if ($data_graph['rounds'][5]['seeds'][0]['teams'][$i]['status'] != 'bye') {

                    // if ($data_graph['rounds'][5]['seeds'][0]['teams'][$i]['name']) {
                    //     $round6[] = self::substrName($data_graph['rounds'][5]['seeds'][0]['teams'][$i]['name']);
                    // } else {
                    //     $round6[] = 'bye';
                    // }
                    $round6[] = $data_graph['rounds'][5]['seeds'][0]['teams'][$i]['name'] ?? 'bye';
                    $round6result[] = $data_graph['rounds'][5]['seeds'][0]['teams'][$i]['result'] ?? '-';
                    $round6status[] = $data_graph['rounds'][5]['seeds'][0]['teams'][$i]['status'];
                } else {
                    $round6result[] = '-';
                    $round6[] = 'bye';
                    $round6status[] = 'wait';
                }
            }
        }

        return array(
            '$round1' => $round1,
            '$round2' => $round2,
            '$round3' => $round3,
            '$round4' => $round4,
            '$round5' => $round5,
            '$round6' => $round6,
            '$round1result' => $round1result,
            '$round2result' => $round2result,
            '$round3result' => $round3result,
            '$round4result' => $round4result,
            '$round5result' => $round5result,
            '$round6result' => $round6result,
            '$round1status' => $round1status,
            '$round2status' => $round2status,
            '$round3status' => $round3status,
            '$round4status' => $round4status,
            '$round5status' => $round5status,
            '$round6status' => $round6status,
        );
    }

    public static function renderPageGraphTeamOfBigFour($view_path, $data, $competition = null, $category = null, $logo_event, $logo_archery, $event_name_report = null, $event_location_report = null, $event_date_report = null)
    {
        return view($view_path, [
            'competition' => $competition,
            'category' => $category,
            'event_name_report' => $event_name_report,
            'event_location_report' => $event_location_report,
            'event_date_report' => $event_date_report,
            'logo_event' => $logo_event,
            'logo_archery' => $logo_archery,

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

            'round1member1status' => $data['$round1status'][0],
            'round1member2status' => $data['$round1status'][1],
            'round1member3status' => $data['$round1status'][2],
            'round1member4status' => $data['$round1status'][3],
            'round2member1status' => $data['$round2status'][0],
            'round2member2status' => $data['$round2status'][1],
            'round3member1status' => $data['$round3status'][0],
            'round3member2status' => $data['$round3status'][1],


        ]);
    }

    public static function renderPageGraphTeamOfBigEight($view_path, $data, $competition = null, $category = null, $logo_event, $logo_archery, $event_name_report = null, $event_location_report = null, $event_date_report = null)
    {
        return view($view_path, [
            'competition' => $competition,
            'category' => $category,
            'event_name_report' => $event_name_report,
            'event_location_report' => $event_location_report,
            'event_date_report' => $event_date_report,
            'logo_event' => $logo_event,
            'logo_archery' => $logo_archery,

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

    public static function renderPageGraphIndividuOfBigSixteen($view_path, $data, $competition = null, $category = null, $logo_event, $logo_archery, $event_name_report = null, $event_location_report = null, $event_date_report = null)
    {
        return view($view_path, [
            'competition' => $competition,
            'category' => $category,
            'event_name_report' => $event_name_report,
            'event_location_report' => $event_location_report,
            'event_date_report' => $event_date_report,
            'logo_event' => $logo_event,
            'logo_archery' => $logo_archery,

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
            'round5member1' => $data['$round5'][0],
            'round5member2' => $data['$round5'][1],

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

        ]);
    }

    public static function renderPageGraphIndividuOfBigTwentyTwo($view_path, $data, $competition = null, $category = null, $logo_event, $logo_archery, $event_name_report = null, $event_location_report = null, $event_date_report = null)
    {
        return view($view_path, [
            'competition' => $competition,
            'category' => $category,
            'event_name_report' => $event_name_report,
            'event_location_report' => $event_location_report,
            'event_date_report' => $event_date_report,
            'logo_event' => $logo_event,
            'logo_archery' => $logo_archery,

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
            'round1member17' => $data['$round1'][16],
            'round1member18' => $data['$round1'][17],
            'round1member19' => $data['$round1'][18],
            'round1member20' => $data['$round1'][19],
            'round1member21' => $data['$round1'][20],
            'round1member22' => $data['$round1'][21],
            'round1member23' => $data['$round1'][22],
            'round1member24' => $data['$round1'][23],
            'round1member25' => $data['$round1'][24],
            'round1member26' => $data['$round1'][25],
            'round1member27' => $data['$round1'][26],
            'round1member28' => $data['$round1'][27],
            'round1member29' => $data['$round1'][28],
            'round1member30' => $data['$round1'][29],
            'round1member31' => $data['$round1'][30],
            'round1member32' => $data['$round1'][31],
            'round2member1' => $data['$round2'][0],
            'round2member2' => $data['$round2'][1],
            'round2member3' => $data['$round2'][2],
            'round2member4' => $data['$round2'][3],
            'round2member5' => $data['$round2'][4],
            'round2member6' => $data['$round2'][5],
            'round2member7' => $data['$round2'][6],
            'round2member8' => $data['$round2'][7],
            'round2member9' => $data['$round2'][8],
            'round2member10' => $data['$round2'][9],
            'round2member11' => $data['$round2'][10],
            'round2member12' => $data['$round2'][11],
            'round2member13' => $data['$round2'][12],
            'round2member14' => $data['$round2'][13],
            'round2member15' => $data['$round2'][14],
            'round2member16' => $data['$round2'][15],
            'round3member1' => $data['$round3'][0],
            'round3member2' => $data['$round3'][1],
            'round3member3' => $data['$round3'][2],
            'round3member4' => $data['$round3'][3],
            'round3member5' => $data['$round3'][4],
            'round3member6' => $data['$round3'][5],
            'round3member7' => $data['$round3'][6],
            'round3member8' => $data['$round3'][7],
            'round4member1' => $data['$round4'][0],
            'round4member2' => $data['$round4'][1],
            'round4member3' => $data['$round4'][2],
            'round4member4' => $data['$round4'][3],
            'round5member1' => $data['$round5'][0],
            'round5member2' => $data['$round5'][1],
            'round6member1' => $data['$round6'][0],
            'round6member2' => $data['$round6'][1],

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
            'round1member17result' => $data['$round1result'][16],
            'round1member18result' => $data['$round1result'][17],
            'round1member19result' => $data['$round1result'][18],
            'round1member20result' => $data['$round1result'][19],
            'round1member21result' => $data['$round1result'][20],
            'round1member22result' => $data['$round1result'][21],
            'round1member23result' => $data['$round1result'][22],
            'round1member24result' => $data['$round1result'][23],
            'round1member25result' => $data['$round1result'][24],
            'round1member26result' => $data['$round1result'][25],
            'round1member27result' => $data['$round1result'][26],
            'round1member28result' => $data['$round1result'][27],
            'round1member29result' => $data['$round1result'][28],
            'round1member30result' => $data['$round1result'][29],
            'round1member31result' => $data['$round1result'][30],
            'round1member32result' => $data['$round1result'][31],
            'round2member1result' => $data['$round2result'][0],
            'round2member2result' => $data['$round2result'][1],
            'round2member3result' => $data['$round2result'][2],
            'round2member4result' => $data['$round2result'][3],
            'round2member5result' => $data['$round2result'][4],
            'round2member6result' => $data['$round2result'][5],
            'round2member7result' => $data['$round2result'][6],
            'round2member8result' => $data['$round2result'][7],
            'round2member9result' => $data['$round2result'][8],
            'round2member10result' => $data['$round2result'][9],
            'round2member11result' => $data['$round2result'][10],
            'round2member12result' => $data['$round2result'][11],
            'round2member13result' => $data['$round2result'][12],
            'round2member14result' => $data['$round2result'][13],
            'round2member15result' => $data['$round2result'][14],
            'round2member16result' => $data['$round2result'][15],
            'round3member1result' => $data['$round3result'][0],
            'round3member2result' => $data['$round3result'][1],
            'round3member3result' => $data['$round3result'][2],
            'round3member4result' => $data['$round3result'][3],
            'round3member5result' => $data['$round3result'][4],
            'round3member6result' => $data['$round3result'][5],
            'round3member7result' => $data['$round3result'][6],
            'round3member8result' => $data['$round3result'][7],
            'round4member1result' => $data['$round4result'][0],
            'round4member2result' => $data['$round4result'][1],
            'round4member3result' => $data['$round4result'][2],
            'round4member4result' => $data['$round4result'][3],
            'round5member1result' => $data['$round5result'][0],
            'round5member2result' => $data['$round5result'][1],
            'round6member1result' => $data['$round6result'][0],
            'round6member2result' => $data['$round6result'][1],

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
            'round1member17status' => $data['$round1status'][16],
            'round1member18status' => $data['$round1status'][17],
            'round1member19status' => $data['$round1status'][18],
            'round1member20status' => $data['$round1status'][19],
            'round1member21status' => $data['$round1status'][20],
            'round1member22status' => $data['$round1status'][21],
            'round1member23status' => $data['$round1status'][22],
            'round1member24status' => $data['$round1status'][23],
            'round1member25status' => $data['$round1status'][24],
            'round1member26status' => $data['$round1status'][25],
            'round1member27status' => $data['$round1status'][26],
            'round1member28status' => $data['$round1status'][27],
            'round1member29status' => $data['$round1status'][28],
            'round1member30status' => $data['$round1status'][29],
            'round1member31status' => $data['$round1status'][30],
            'round1member32status' => $data['$round1status'][31],
            'round2member1status' => $data['$round2status'][0],
            'round2member2status' => $data['$round2status'][1],
            'round2member3status' => $data['$round2status'][2],
            'round2member4status' => $data['$round2status'][3],
            'round2member5status' => $data['$round2status'][4],
            'round2member6status' => $data['$round2status'][5],
            'round2member7status' => $data['$round2status'][6],
            'round2member8status' => $data['$round2status'][7],
            'round2member9status' => $data['$round2status'][8],
            'round2member10status' => $data['$round2status'][9],
            'round2member11status' => $data['$round2status'][10],
            'round2member12status' => $data['$round2status'][11],
            'round2member13status' => $data['$round2status'][12],
            'round2member14status' => $data['$round2status'][13],
            'round2member15status' => $data['$round2status'][14],
            'round2member16status' => $data['$round2status'][15],
            'round3member1status' => $data['$round3status'][0],
            'round3member2status' => $data['$round3status'][1],
            'round3member3status' => $data['$round3status'][2],
            'round3member4status' => $data['$round3status'][3],
            'round3member5status' => $data['$round3status'][4],
            'round3member6status' => $data['$round3status'][5],
            'round3member7status' => $data['$round3status'][6],
            'round3member8status' => $data['$round3status'][7],
            'round4member1status' => $data['$round4status'][0],
            'round4member2status' => $data['$round4status'][1],
            'round4member3status' => $data['$round4status'][2],
            'round4member4status' => $data['$round4status'][3],
            'round5member1status' => $data['$round5status'][0],
            'round5member2status' => $data['$round5status'][1],
            'round6member1status' => $data['$round6status'][0],
            'round6member2status' => $data['$round6status'][1],

        ]);
    }

    private static function substrName($name)
    {
        $split_name    = explode(' ', $name);

        if (ctype_upper($split_name[0]) == true) {
            return (strlen($name) > 14) ? substr($name, 0, 11) . '...' : $name;
        } else {
            return (strlen($name) > 16) ? substr($name, 0, 14) . '...' : $name;
        }
    }
}
