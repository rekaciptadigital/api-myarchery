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
        for ($a = 0; $a <= $first_loop-1; $a++) {
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

}