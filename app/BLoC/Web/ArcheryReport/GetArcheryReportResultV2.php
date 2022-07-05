<?php

namespace App\BLoC\Web\ArcheryReport;

use App\Models\ArcheryEventEliminationMember;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryClub;
use Mpdf\Mpdf;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventEliminationSchedule;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryMasterTeamCategory;
use DAI\Utils\Helpers\BLoC;
use App\Models\ArcheryScoring;
use App\Models\ArcheryEventElimination;
use App\Http\Services\PDFService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;
use Response;
use PDFv2;
use Illuminate\Support\Facades\Redis;
use App\Libraries\EliminationFormatPDF;
use App\Libraries\EliminationFormatPDFV2;
use App\BLoC\Web\EventElimination\GetEventEliminationTemplate;
use Illuminate\Support\Collection;
use App\Models\ArcheryEventQualificationTime;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventEliminationGroupMatch;
use App\Models\ArcheryEventEliminationGroupTeams;


class GetArcheryReportResultV2 extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {

        $admin = Auth::user();
        $event_id = $parameters->get('event_id');
        $date_filter = $parameters->get('date');
        // $id = array();

        $pages = array();
        $logo_event = '<img src="'.Storage::disk('public')->path('logo/logo-event-series-2.png').'" alt="" width="80%"></img>';
        $logo_archery = '<img src="'.Storage::disk('public')->path("logo/logo-archery.png").'" alt="" width="80%"></img>';
        $event_name_report = 'PRO JAKARTA OPEN 2022';
        $event_date_report = '2 Juli 2022 - 6 Juli 2022';
        $event_location_report = 'Jakarta International Equestrian Park';

        $competition_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct competition_category_id as competition_category'))->where("event_id", $event_id)
            ->orderBy('competition_category_id', 'DESC')->get();

        if (!$competition_category) throw new BLoCException("tidak ada data kategori terdaftar untuk event tersebut");

        // ------------------------------------------ PRINT COVER ------------------------------------------ //
        $logo_event_cover = '<img src="'.Storage::disk('public')->path("logo/logo-event-series-2.png").'" alt="" width="25%"></img>';
        $logo_archery_cover = '<img src="'.Storage::disk('public')->path("logo/logo-archery.png").'" alt="" width="60%"></img>';
        $pages[] = view('report_result/cover', [
            'cover_event' => $logo_event_cover,
            'logo_archery' => $logo_archery_cover,
            'event_name_report' => $event_name_report,
            'event_date_report' => $event_date_report,
            'event_location_report' => $event_location_report
        ]);
        // ------------------------------------------ END PRINT COVER ------------------------------------------ //

        foreach ($competition_category as $competition) {
            // $competition->competition_category = 'Compound';
            $age_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct age_category_id as age_category'))->where("event_id", $event_id)
                ->where("competition_category_id", $competition->competition_category)
                ->orderBy('competition_category_id', 'DESC')->get();

            if (!$age_category) throw new BLoCException("tidak ada data age category terdaftar untuk event tersebut");

            foreach ($age_category as $age) {
                // $age->age_category = 'Umum';
                $distance_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct distance_id as distance_category'))->where("event_id", $event_id)
                    ->where("competition_category_id", $competition->competition_category)
                    ->where("age_category_id", $age->age_category)
                    ->orderBy('competition_category_id', 'DESC')->get();

                if (!$distance_category) throw new BLoCException("tidak ada data distance category terdaftar untuk event tersebut");


                foreach ($distance_category as $distance) {
                    // $distance->distance_category = '50';
                    $team_category = ArcheryEventCategoryDetail::select(DB::RAW('team_category_id as team_category'), DB::RAW('archery_event_category_details.id as category_detail_id'))->where("event_id", $event_id)
                        ->where("competition_category_id", $competition->competition_category)
                        ->where("age_category_id", $age->age_category)
                        ->where("distance_id", $distance->distance_category)
                        ->leftJoin("archery_master_team_categories", 'archery_master_team_categories.id', 'archery_event_category_details.team_category_id')
                        ->orderBy("archery_master_team_categories.short", "ASC")->get();
                    if (!$team_category) throw new BLoCException("tidak ada data team category terdaftar untuk event tersebut");


                    foreach ($team_category as $team) {
                        // $team->category_detail_id = 108;
                        $category_detail = ArcheryEventCategoryDetail::find($team->category_detail_id);
                        if (!$category_detail) throw new BLoCException("category not found");

                        $date_now = date("Y-m-d H:i:s");
                        $qualification_time = ArcheryEventQualificationTime::where('category_detail_id', $category_detail->id)->whereDate("event_end_datetime", '<', $date_now)->first(); // report all category in event
                        // $qualification_time = ArcheryEventQualificationTime::where('category_detail_id', $category_detail->id)->whereDate("event_end_datetime", $date_filter)->first(); //report harian

                        $data_elimination = $this->getElimination($category_detail);
                        $data_qualification = $this->getQualification($category_detail);

                        if($qualification_time) {
                            // $id[] = $category_detail->id;
                            if (!empty($data_qualification)) {
                                $category_of_team = ArcheryMasterTeamCategory::find($category_detail->team_category_id);
                                if (!$category_of_team) throw new BLoCException("team category not found");

                                // ------------------------------------------ ELIMINATION ------------------------------------------ //
                                $type = 'elimination';
                                $report = $competition->competition_category . ' - Elimination';
                                $data_report = $this->getData($category_detail->id, $type, $event_id);

                                if (!empty($data_report[0])) {
                                    // if (strtolower($category_of_team->type) == "individual") {
                                        $pages[] = view('report_result/elimination', [
                                            'data_report' => $data_report[0],
                                            'competition' => $competition->competition_category,
                                            'report' => $report,
                                            'category' => $data_report[0][0]['category'],
                                            'logo_event' => $logo_event,
                                            'logo_archery' => $logo_archery,
                                            'type' => ucfirst($type),
                                            'event_name_report' => $event_name_report,
                                            'event_date_report' => $event_date_report,
                                            'event_location_report' => $event_location_report
                                        ]);
                                        $data_report = array();
                                    // }
                                }

                                    if (strtolower($category_of_team->type) == "team") {
                                        $data_elimination_team = $this->getDataEliminationTeam($category_detail->id);
                                        if (!empty($data_elimination_team)) {
                                            $pages[] = view('report_result/elimination_team', [
                                                'data_report' => $data_elimination_team,
                                                'competition' => $competition->competition_category,
                                                'report' => $report,
                                                'category' => ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id),
                                                'logo_event' => $logo_event,
                                                'logo_archery' => $logo_archery,
                                                'type' => ucfirst($type),
                                                'event_name_report' => $event_name_report,
                                                'event_date_report' => $event_date_report,
                                                'event_location_report' => $event_location_report
                                            ]);
                                            $data_report = array();
                                        }
                                        
                                    }
                                    
                                // ------------------------------------------ END ELIMINATION ------------------------------------------ //
    

                                // ------------------------------------------ QUALIFICATION ------------------------------------------ //
                                $type = 'qualification';
                                $report = $competition->competition_category . ' - Qualification';
                                $data_report = $this->getData($category_detail->id, $type, $event_id);
    
                                    if (strtolower($category_of_team->type) == "individual") {
                                        if (!empty($data_report[0])) {
                                            $pages[] = view('report_result/qualification', [
                                                'data_report' => $data_report[0],
                                                'competition' => $competition->competition_category,
                                                'report' => $report,
                                                'category' => $data_report[0][0]['category'],
                                                'logo_event' => $logo_event,
                                                'logo_archery' => $logo_archery,
                                                'type' => ucfirst($type),
                                                'event_name_report' => $event_name_report,
                                                'event_date_report' => $event_date_report,
                                                'event_location_report' => $event_location_report
                                            ]);
                                            $data_report = array();
                                        }
                                    }
                                    
                                    if (strtolower($category_of_team->type) == "team") {
                                        if ($data_elimination['updated'] != false) {
                                            $pages[] = view('report_result/qualification_team', [
                                                'data_report' => $data_qualification,
                                                'competition' => $competition->competition_category,
                                                'report' => $report,
                                                'category' => ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id),
                                                'logo_event' => $logo_event,
                                                'logo_archery' => $logo_archery,
                                                'type' => ucfirst($type),
                                                'event_name_report' => $event_name_report,
                                                'event_date_report' => $event_date_report,
                                                'event_location_report' => $event_location_report
                                            ]);
                                            $data_report = array();
                                        }
                                    }
                                
                                // ------------------------------------------ END QUALIFICATION ------------------------------------------ //
    

                                // ------------------------------------------ ALL RESULTS --------------------------------------- //
                                $type = '';
                                $report = 'Result';
                                $data_report = $this->getData($category_detail->id, $type, $event_id);

                                if (strtolower($category_of_team->type) == "individual") {
                                    if (!empty($data_report[0])) {
        
                                        $elimination_individu = ArcheryEventElimination::where("event_category_id", $category_detail->id)->first();
                                        $data_graph = EliminationFormatPDF::getDataGraph($data_report[1]);
                                
                                        if ($data_elimination['updated'] == false) {
                                            if ($elimination_individu->count_participant == 32) {
                                                $data_graph_individu = EliminationFormatPDFV2::getViewDataGraphIndividuOfBigTwentyTwo($data_elimination);
                                                $view_path = 'report_result/elimination_graph/individu/graph_thirtytwo';
                                                $title_category = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id);
                                                $pages[] = EliminationFormatPDFV2::renderPageGraphIndividuOfBigTwentyTwo($view_path, $data_graph_individu, $competition->competition_category, $title_category, $logo_event, $logo_archery, $event_name_report, $event_location_report, $event_date_report);
                                            } else if ($elimination_individu->count_participant == 16) {
                                                if ($data_graph) {
                                                    $data = EliminationFormatPDF::getViewDataGraph16($data_graph);
                                                    $view_path = 'report_result/graph_sixteen';
                                                    $pages[] = EliminationFormatPDF::renderPageGraph16_reportEvent($view_path, $data, $report, $data_report, $logo_event, $logo_archery, $competition);
                                                }
                                            } else if ($elimination_individu->count_participant == 8) {
                                                if ($data_graph) {
                                                    $data = EliminationFormatPDF::getViewDataGraph8($data_graph);
                                                    $view_path = 'report_result/graph_eight';
                                                    $pages[] = EliminationFormatPDF::renderPageGraph8_reportEvent($view_path, $data, $report, $data_report, $logo_event, $logo_archery, $competition);
                                                }
                                            } else {
                                                continue;
                                            }
                                            
                                        }
    
                                        $pages[] = view('report_result/all_results_individu', [
                                            'data_report' => $data_qualification,
                                            'competition' => $competition->competition_category,
                                            'report' => $report,
                                            'category' => $data_report[0][0]['category'],
                                            'logo_event' => $logo_event,
                                            'logo_archery' => $logo_archery,
                                            'type' => ucfirst($type),
                                            'event_name_report' => $event_name_report,
                                            'event_date_report' => $event_date_report,
                                            'event_location_report' => $event_location_report
                                        ]);
                
                                        $data_report = array();
                                        $data_graph = null;
                                        $data = null;
                                    }
                                }
                                                         
                                if (strtolower($category_of_team->type) == "team") {
                                        $elimination_team = ArcheryEventEliminationGroup::where("category_id", $category_detail->id)->first();

                                        //print bagan eliminasi
                                        if ($data_elimination['updated'] == false) {
                                            if ($elimination_team->count_participant == 4) {
                                                // return ($data_elimination); die;
                                                $data_graph_team = EliminationFormatPDFV2::getViewDataGraphTeamOfBigFour($data_elimination);
                                                $view_path = 'report_result/elimination_graph/team/graph_four';
                                                $title_category = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id);
                                                $pages[] = EliminationFormatPDFV2::renderPageGraphTeamOfBigFour($view_path, $data_graph_team, $competition->competition_category, $title_category, $logo_event, $logo_archery, $event_name_report, $event_location_report, $event_date_report);
                                            }
                                        } else {
                                            continue;
                                        }
                                        //end print bagan eliminasi

                                        //print all result qualification
                                        $pages[] = view('report_result/all_results_team', [
                                            'data_report' => $data_qualification,
                                            'competition' => $competition->competition_category,
                                            'report' => $report,
                                            'category' => ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id),
                                            'logo_event' => $logo_event,
                                            'logo_archery' => $logo_archery,
                                            'type' => ucfirst($type),
                                            'event_name_report' => $event_name_report,
                                            'event_date_report' => $event_date_report,
                                            'event_location_report' => $event_location_report
                                        ]);
                                        $data_report = array();
                                        //end print all result qualification
                                }
                                
                                // ------------------------------------------ END ALL RESULTS ------------------------------------------ //
                                
                            } else {
                                continue;
                            }
                        } else {
                            continue;
                        }
                        
                    }
                }
            }
        }

        $pdf = PDFv2::loadView('report_result/all', ['pages' => $pages]);
        $pdf->setOptions([
            'margin-top'    => 8,
            'margin-bottom' => 1,
            'page-size'     => 'a4',
            'orientation'   => 'portrait',
            'enable-javascript' => true,
            'javascript-delay' => 9000,
            'no-stop-slow-scripts' => true,
            'enable-smart-shrinking' => true,
            'images' => true
        ]);

        $digits = 3;
        $fileName   = 'report_result_' . date("YmdHis") . '.pdf';
        // $fileName   = 'report_result_' . rand(pow(10, $digits - 1), pow(10, $digits) - 1) . '.pdf';
        $path = 'asset/report-result';
        $generate   = $pdf->save('' . $path . '/' . $fileName . '');
        $response = [
            'file_path' => url(env('APP_HOSTNAME') . $path . '/' . $fileName . '')
        ];


        // set generate date of report
        $key = env("REDIS_KEY_PREFIX") . ":report:date-generate:event-" . $event_id . ":updated";
        Redis::hset($key, 'competition', date("Y-m-d"));

        return $response;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => 'required|integer'
        ];
    }

    protected function getData($category_detail_id, $type, $event_id)
    {
        $data_report = [];
        $category_id = null;
        $elimination_rank = 0;

            $members = ArcheryEventEliminationMember::select("*", "archery_event_category_details.id as category_details_id", "archery_event_participant_members.id as participant_member_id", DB::RAW('date(archery_event_elimination_members.created_at) as date'))
                ->join('archery_event_participant_members', 'archery_event_participant_members.id', '=', 'archery_event_elimination_members.member_id')
                ->join('archery_event_participants', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
                ->join('archery_event_category_details', 'archery_event_category_details.id', '=', 'archery_event_participants.event_category_id')
                ->where("archery_event_category_details.id", $category_detail_id)
                ->where("archery_event_participants.event_id", $event_id)
                ->where(function ($query) use ($type) {
                    if ($type == "elimination") {
                        $query->where("archery_event_elimination_members.elimination_ranked", '>', 0);
                        $query->where("archery_event_elimination_members.elimination_ranked", '<=', 3);
                        $query->orderBy('archery_event_elimination_members.elimination_ranked', 'ASC');
                    } else if ($type == "qualification") {
                        $query->where("archery_event_elimination_members.position_qualification", '>', 0);
                        $query->where("archery_event_elimination_members.position_qualification", '<=', 3);
                        $query->orderBy('archery_event_elimination_members.position_qualification', 'ASC');
                    } else {
                        $query->orderBy('archery_event_elimination_members.position_qualification', 'ASC');
                    }
                })
                ->orderBy('archery_event_participants.event_category_id', 'ASC')
                ->orderBy('archery_event_category_details.team_category_id', 'DESC')
                ->get();


            if ($members) {
                foreach ($members as $member) {

                    $categoryLabel = ArcheryEventCategoryDetail::getCategoryLabelComplete($member->category_details_id);

                    // if ($member->elimination_ranked == 1 || $member->position_qualification == 1) {
                    //     $medal = 'Gold';
                    // } else if ($member->elimination_ranked == 2 || $member->position_qualification == 2) {
                    //     $medal = 'Silver';
                    // } else {
                    //     $medal = 'Bronze';
                    // }

                    if ($type == "elimination") {
                        $elimination_rank = $member->elimination_ranked;
                        if ($member->elimination_ranked == 1) {
                            $medal = 'Gold';
                        } else if ($member->elimination_ranked == 2) {
                            $medal = 'Silver';
                        } else {
                            $medal = 'Bronze';
                        }
                    } elseif ($type == "qualification") {
                        if ($member->position_qualification == 1) {
                            $medal = 'Gold';
                        } else if ($member->position_qualification == 2) {
                            $medal = 'Silver';
                        } else {
                            $medal = 'Bronze';
                        }
                    } else {
                        $medal = '-';
                    }

                    $athlete = $member->name;
                    $date = $member->date;

                    $club = ArcheryClub::find($member->club_id);
                    if (!$club) {
                        $club = '';
                    } else {
                        $club = $club->name;
                    }

                    $category = ArcheryEventCategoryDetail::find($member->category_details_id);
                    $session = [];
                    for ($i = 0; $i < $category->session_in_qualification; $i++) {
                        $session[] = $i + 1;
                    }
                    $scoring = ArcheryScoring::generateScoreBySession($member->participant_member_id, 1, $session);
                    
                    $data_report[] = array("athlete" => $athlete, "club" => $club, "category" => $categoryLabel, "medal" => $medal, "date" => $date, "scoring" => $scoring, "elimination_rank" => $elimination_rank);

                    $category_id = $member->category_details_id;
                }
            }
        
        if ($type == "elimination") {
            $sorted_data = collect($data_report)->sortBy('elimination_rank')->values()->all();
            return array($sorted_data, $category_id);
        }

        $sorted_data = collect($data_report)->sortByDesc('scoring.total')->values()->all();

        return array($sorted_data, $category_id);
    }

    protected function getElimination($category_detail) {

        $team_category = ArcheryMasterTeamCategory::find($category_detail->team_category_id);
        if (!$team_category) throw new BLoCException("team category not found");

        if (strtolower($team_category->type) == "team") {
            $data = app('App\BLoC\Web\EventElimination\GetEventEliminationTemplate')->getTemplateTeam($category_detail);
        }

        if (strtolower($team_category->type) == "individual") {
            $data = app('App\BLoC\Web\EventElimination\GetEventEliminationTemplate')->getTemplateIndividu($category_detail);
        }

        return $data;
    }

    protected function getQualification($category_detail) {
        $score_type = 1;
        $name = null;
        $team_category = ArcheryMasterTeamCategory::find($category_detail->team_category_id);
        if (!$team_category) throw new BLoCException("team category not found");

        $event = ArcheryEvent::find($category_detail->event_id);
        if (!$event) throw new BLoCException("CATEGORY INVALID");

        $session = [];
        for ($i = 0; $i < $category_detail->session_in_qualification; $i++) {
            $session[] = $i + 1;
        }

        if ($category_detail->category_team == "Individual") {
            // $data = app('App\BLoC\Web\ArcheryScoring\GetParticipantScoreQualificationV2')->getListMemberScoringIndividual($category_detail->id, $score_type, $session, $name, $event->id);
            $qualification_member = ArcheryScoring::getScoringRankByCategoryId($category_detail->id, $score_type, $session, false, $name);

            return $qualification_member;
        }

        if (strtolower($team_category->type) == "team") {
            if ($team_category->id == "mix_team") {
                $data = app('App\BLoC\Web\ArcheryScoring\GetParticipantScoreQualificationV2')->mixTeamBestOfThree($category_detail, $team_category, $session);
            } else {
                $data = app('App\BLoC\Web\ArcheryScoring\GetParticipantScoreQualificationV2')->teamBestOfThree($category_detail, $team_category, $session);
            }
        }

        return $data;
    }

    protected function getDataEliminationTeam($category_detail_id)
    {
        $elimination_group = ArcheryEventEliminationGroup::where('category_id' , $category_detail_id)->first();
        if ($elimination_group) {
            $elimination_group_match = ArcheryEventEliminationGroupMatch::select(DB::RAW('distinct group_team_id as teamid'))->where('elimination_group_id', $elimination_group->id)->get();

            $data = array();
            foreach ($elimination_group_match as $key => $value) {
    
                $elimination_group_team = ArcheryEventEliminationGroupTeams::where('id', $value->teamid)->first();

                if ($elimination_group_team) {
                    if ($elimination_group_team->elimination_ranked <= 3) {
                        $data[] = [
                            'id' => $elimination_group_team->id,
                            'team_name' => $elimination_group_team->team_name,
                            'elimination_ranked' => $elimination_group_team->elimination_ranked ?? 0,
                            'category' => ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail_id),
                            'date' => $elimination_group->created_at->format('Y-m-d')
                        ];
                    } else {
                        continue;
                    }
                }
                
            }
    
            $sorted_data = collect($data)->sortBy('elimination_ranked')->values()->take(3);
            return $sorted_data;
        }
    }
}
