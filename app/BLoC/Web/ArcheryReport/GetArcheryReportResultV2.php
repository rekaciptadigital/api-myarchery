<?php

namespace App\BLoC\Web\ArcheryReport;

use App\Models\ArcheryEventEliminationMember;
use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\ArcheryMasterTeamCategory;
use App\Models\ArcheryScoring;
use App\Models\ArcheryEventElimination;
use Illuminate\Support\Facades\Storage;
use PDFv2;
use Illuminate\Support\Facades\Redis;
use App\Libraries\EliminationFormatPDF;
use App\Libraries\EliminationFormatPDFV2;
use App\Models\ArcheryEventQualificationTime;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventEliminationGroupMatch;
use App\Models\ArcheryEventEliminationGroupTeams;
use App\Libraries\ClubRanked;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryMasterAgeCategory;
use App\Models\UrlReport;
use Illuminate\Support\Carbon;

class GetArcheryReportResultV2 extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get('event_id');

        // $check_is_exist_report = UrlReport::where("event_id", $event_id)->where("type", "report_event")->first();
        // if ($check_is_exist_report) {
        //     return [
        //         "file_path" => $check_is_exist_report->url
        //     ];
        // }

        $pages = array();
        $logo_archery = '<img src="' . Storage::disk('public')->path("logo/logo-archery.png") . '" alt="" width="80%"></img>';

        $archery_event = ArcheryEvent::find($event_id);
        if (!$archery_event) {
            throw new BLoCException("event tidak terdaftar");
        }
        $logo_event = $archery_event->logo;

        $event_name_report = $archery_event->event_name;
        $start_date_event = dateFormatTranslate(Carbon::parse($archery_event->event_start_datetime)->format('d-F-Y'), false);
        $end_date_event = dateFormatTranslate(Carbon::parse($archery_event->event_end_datetime)->format('d-F-Y'), false);
        $event_date_report = $start_date_event . ' - ' . $end_date_event;
        $event_location_report = $archery_event->location;

        $competition_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct competition_category_id as competition_category'))->where("event_id", $event_id)
            ->orderBy('competition_category_id', 'DESC')->get();

        if (!$competition_category) throw new BLoCException("tidak ada data kategori terdaftar untuk event tersebut");

        // ------------------------------------------ PRINT COVER ------------------------------------------ //
        $logo_archery_cover = '<img src="' . Storage::disk('public')->path("logo/logo-archery.png") . '" alt="" width="60%"></img>';
        $cover_page = view('report_result/cover', [
            'cover_event' => $logo_event,
            'logo_archery' => $logo_archery_cover,
            'event_name_report' => $event_name_report,
            'event_date_report' => $event_date_report,
            'event_location_report' => $event_location_report
        ]);
        // ------------------------------------------ END PRINT COVER ------------------------------------------ //


        // ------------------------------------------ PRINT FOOTER ------------------------------------------ //
        $footer_html = view('report_result/footer');
        // ------------------------------------------ END PRINT FOOTER ------------------------------------------ //


        // ------------------------------------------ PRINT MEDAL STANDING ------------------------------------------ //
        $data_medal_standing = ArcheryEventParticipant::getMedalStanding($event_id);
        if (count($data_medal_standing) > 0) {
            $pages[] = view('report_result/club_rank_medals_standing', [
                'logo_event' => $logo_event,
                'logo_archery' => $logo_archery,
                'event_name_report' => $event_name_report,
                'event_date_report' => $event_date_report,
                'event_location_report' => $event_location_report,
                'headers' => $data_medal_standing['title_header']['category'],
                'datatables' => $data_medal_standing['datatable'],
                'total_medal_by_category' => $data_medal_standing['total_medal_by_category'],
                'total_medal_by_category_all_club' => $data_medal_standing['total_medal_by_category_all_club']
            ]);
        }
        // ------------------------------------------ END PRINT MEDAL STANDING ------------------------------------------ //

        foreach ($competition_category as $competition) {
            // $competition->competition_category = 'Nasional';
            $age_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct age_category_id as age_category'))->where("event_id", $event_id)
                ->where("competition_category_id", $competition->competition_category)
                ->orderBy('competition_category_id', 'DESC')->get();

            if (!$age_category) throw new BLoCException("tidak ada data age category terdaftar untuk event tersebut");

            foreach ($age_category as $age) {
                // $age->age_category = 'U-12';
                $distance_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct distance_id as distance_category'))->where("event_id", $event_id)
                    ->where("competition_category_id", $competition->competition_category)
                    ->where("age_category_id", $age->age_category)
                    ->orderBy('competition_category_id', 'DESC')->get();

                if (!$distance_category) throw new BLoCException("tidak ada data distance category terdaftar untuk event tersebut");


                foreach ($distance_category as $distance) {
                    // $distance->distance_category = '15';
                    $team_category = ArcheryEventCategoryDetail::select(DB::RAW('team_category_id as team_category'), DB::RAW('archery_event_category_details.id as category_detail_id'))->where("event_id", $event_id)
                        ->where("competition_category_id", $competition->competition_category)
                        ->where("age_category_id", $age->age_category)
                        ->where("distance_id", $distance->distance_category)
                        ->leftJoin("archery_master_team_categories", 'archery_master_team_categories.id', 'archery_event_category_details.team_category_id')
                        ->orderBy("archery_master_team_categories.short", "ASC")->get();
                    if (!$team_category) throw new BLoCException("tidak ada data team category terdaftar untuk event tersebut");


                    foreach ($team_category as $team) {
                        // $team->category_detail_id = 136;
                        $category_detail = ArcheryEventCategoryDetail::find($team->category_detail_id);
                        if (!$category_detail) throw new BLoCException("category not found");

                        $data_elimination = ArcheryEventParticipant::getElimination($category_detail);
                        $data_qualification = ArcheryEventParticipant::getQualification($category_detail);  // dapatkan list peringkat qualifikasi individu dan beregu

                        // $id[] = $category_detail->id;
                        if (count($data_qualification) > 0) {
                            $category_of_team = ArcheryMasterTeamCategory::find($category_detail->team_category_id);
                            if (!$category_of_team) {
                                throw new BLoCException("team category not found");
                            }

                            // ------------------------------------------ ELIMINATION ------------------------------------------ //
                            $type = 'elimination';
                            $report = $competition->competition_category . ' - Elimination';

                            if (strtolower($category_of_team->type) == "individual") {
                                $data_report = ArcheryEventParticipant::getData($category_detail->id, $type, $event_id); // dapatkan data qualifikasi atau eliminasi dari 1 - 3 individu
                                if (count($data_report[0]) > 0) {
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
                                }
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
                                }
                            }

                            // ------------------------------------------ END ELIMINATION ------------------------------------------ //


                            // ------------------------------------------ QUALIFICATION ------------------------------------------ //
                            $type = 'qualification';
                            $report = $competition->competition_category . ' - Qualification';

                            if (strtolower($category_of_team->type) == "individual") {
                                $data_report = ArcheryEventParticipant::getData($category_detail->id, $type, $event_id);
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
                                }
                            }

                            if (strtolower($category_of_team->type) == "team") {
                                $data_elimination_team = $this->getDataEliminationTeam($category_detail->id);
                                if (!empty($data_elimination_team)) {
                                    // print qualification team yang ada round eliminasi
                                    if (isset($data_elimination['updated']) && $data_elimination['updated'] != false) {
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
                                    }
                                } else {
                                    // print qualification team yang tidak ada round eliminasi (eliminasi = qualification)
                                    $pages[] = view('report_result/qualification_team_without_elimination', [
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
                                }
                            }

                            // ------------------------------------------ END QUALIFICATION ------------------------------------------ //


                            // ------------------------------------------ ALL RESULTS --------------------------------------- //
                            $type = '';
                            $report = 'Result';
                            $data_report = ArcheryEventParticipant::getData($category_detail->id, $type, $event_id);


                            if (strtolower($category_of_team->type) == "individual") {
                                if (!empty($data_report[0])) {

                                    $elimination_individu = ArcheryEventElimination::where("event_category_id", $category_detail->id)->first();
                                    $data_graph = EliminationFormatPDF::getDataGraph($data_report[1]);

                                    if (isset($data_elimination['updated']) && $data_elimination['updated'] == false) {
                                        if ($elimination_individu->count_participant == 32) {
                                            $data_graph_individu = EliminationFormatPDFV2::getViewDataGraphIndividuOfBigTwentyTwo($data_elimination);
                                            $view_path = 'report_result/elimination_graph/individu/graph_thirtytwo';
                                            $title_category = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id);
                                            $pages[] = EliminationFormatPDFV2::renderPageGraphIndividuOfBigTwentyTwo($view_path, $data_graph_individu, $competition->competition_category, $title_category, $logo_event, $logo_archery, $event_name_report, $event_location_report, $event_date_report);
                                        } else if ($elimination_individu->count_participant == 16) {
                                            $data_graph_individu = EliminationFormatPDFV2::getViewDataGraphIndividuOfBigSixteen($data_elimination);
                                            $view_path = 'report_result/elimination_graph/individu/graph_sixteen';
                                            $title_category = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id);
                                            $pages[] = EliminationFormatPDFV2::renderPageGraphIndividuOfBigSixteen($view_path, $data_graph_individu, $competition->competition_category, $title_category, $logo_event, $logo_archery, $event_name_report, $event_location_report, $event_date_report);
                                        } else if ($elimination_individu->count_participant == 8) {
                                            if ($data_graph) {
                                                $data = EliminationFormatPDF::getViewDataGraph8($data_graph);
                                                $view_path = 'report_result/graph_eight';
                                                $pages[] = EliminationFormatPDF::renderPageGraph8_reportEvent($view_path, $data, $report, $data_report, $logo_event, $logo_archery, $competition, $event_name_report, $event_location_report, $event_date_report);
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
                                if (isset($data_elimination['updated']) && $data_elimination['updated'] == false) {
                                    if ($elimination_team->count_participant == 4) {
                                        // return ($data_elimination); die;
                                        $data_graph_team = EliminationFormatPDFV2::getViewDataGraphTeamOfBigFour($data_elimination);
                                        $view_path = 'report_result/elimination_graph/team/graph_four';
                                        $title_category = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id);
                                        $pages[] = EliminationFormatPDFV2::renderPageGraphTeamOfBigFour($view_path, $data_graph_team, $competition->competition_category, $title_category, $logo_event, $logo_archery, $event_name_report, $event_location_report, $event_date_report);
                                    } else if ($elimination_team->count_participant == 8) {
                                        $data_graph_team = EliminationFormatPDFV2::getViewDataGraphTeamOfBigEight($data_elimination);
                                        $view_path = 'report_result/elimination_graph/team/graph_eight';
                                        $title_category = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id);
                                        $pages[] = EliminationFormatPDFV2::renderPageGraphTeamOfBigEight($view_path, $data_graph_team, $competition->competition_category, $title_category, $logo_event, $logo_archery, $event_name_report, $event_location_report, $event_date_report);
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
                    }
                }
            }
        }

        $pdf = PDFv2::loadView('report_result/all', ['pages' => $pages]);
        $pdf->setOptions([
            'margin-top'    => 8,
            'margin-bottom' => 12,
            'page-size'     => 'a4',
            'orientation'   => 'portrait',
            'enable-javascript' => true,
            'javascript-delay' => 9000,
            'no-stop-slow-scripts' => true,
            'enable-smart-shrinking' => true,
            'images' => true,
            'cover' => $cover_page,
            // 'header-html' => $header_html,
            // 'footer-html' => $footer_html,
            // 'toc' => true,
            // 'toc-level-indentation' => '2rem',
            // 'enable-toc-back-links' => true,
        ]);

        $digits = 3;
        $fileName   = 'report_result_' . date("YmdHis") . '.pdf';
        // $fileName   = 'report_result_' . rand(pow(10, $digits - 1), pow(10, $digits) - 1) . '.pdf';
        $path = 'asset/report-result';
        $generate   = $pdf->save('' . $path . '/' . $fileName . '');
        $response = [
            'file_path' => url(env('APP_HOSTNAME') . $path . '/' . $fileName . '')
        ];

        $url_report = new UrlReport();
        $url_report->url = $response["file_path"];
        $url_report->type = "report_event";
        $url_report->event_id = $event_id;
        $url_report->save();


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

    protected function getMedalStanding($event_id)
    {
        $data = ClubRanked::getEventRanked($event_id, 1, null);
        if (count($data) > 0) {
            $title_header = array();
            $competition_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct competition_category_id as competition_category'))->where("event_id", $event_id)
                ->orderBy('competition_category_id', 'DESC')->get();

            foreach ($competition_category as $competition) {
                $age_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct age_category_id as age_category'))->where("event_id", $event_id)
                    ->where("competition_category_id", $competition->competition_category)
                    ->orderBy('competition_category_id', 'DESC')->get();

                foreach ($age_category as $age) {
                    $master_age_category = ArcheryMasterAgeCategory::find($age->age_category);
                    $title_header['category'][$competition->competition_category]['age_category'][$master_age_category->label] = [
                        'gold' => null,
                        'silver' => null,
                        'bronze' => null,
                    ];
                }

                // colspan header title
                $count_colspan = [
                    'count_colspan' => count($age_category) * 3
                ];
                array_push($title_header['category'][$competition->competition_category], $count_colspan);
            }

            $result = [];
            $detail_club_with_medal_response = [];
            foreach ($data as $key => $d) {
                $detail_club_with_medal_response["club_name"] = $d["club_name"];
                $detail_club_with_medal_response["total_gold"] = $d["gold"];
                $detail_club_with_medal_response["total_silver"] = $d["silver"];
                $detail_club_with_medal_response["total_bronze"] = $d["bronze"];

                foreach ($competition_category as $competition) {
                    $age_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct age_category_id as age_category'))->where("event_id", $event_id)
                        ->where("competition_category_id", $competition->competition_category)
                        ->orderBy('competition_category_id', 'DESC')->get();

                    foreach ($age_category as $age) {
                        $gold = 0;
                        $silver = 0;
                        $bronze = 0;

                        if (isset($d["detail_medal"]["category"][$competition->competition_category][$age->age_category])) {
                            $gold += $d["detail_medal"]["category"][$competition->competition_category][$age->age_category]["gold"] ?? 0;
                            $silver += $d["detail_medal"]["category"][$competition->competition_category][$age->age_category]["silver"] ?? 0;
                            $bronze += $d["detail_medal"]["category"][$competition->competition_category][$age->age_category]["bronze"] ?? 0;
                        };

                        $detail_club_with_medal_response['category'][$competition->competition_category]['age_category'][$age->age_category] = [
                            "gold" => $gold,
                            "silver" => $silver,
                            "bronze" => $bronze
                        ];
                    }
                }
                $medal_array = [];
                foreach ($detail_club_with_medal_response["category"] as $c) {
                    foreach ($c as $a) {
                        foreach ($a as $s) {
                            foreach ($s as $b) {
                                array_push($medal_array, $b);
                            }
                        }
                    }
                }
                $detail_club_with_medal_response["medal_array"] = $medal_array;
                array_push($result, $detail_club_with_medal_response);
            }

            // start: total medal emas, perak, perunggu dari setiap kategori semua klub
            $array_of_total_medal_by_category = [];
            $total_array_category = count($result[0]['medal_array']);
            for ($i = 0; $i < $total_array_category; $i++) {
                $total_medal_by_category = 0;
                for ($j = 0; $j < count($result); $j++) {
                    $total_medal_by_category += $result[$j]['medal_array'][$i];
                }
                array_push($array_of_total_medal_by_category, $total_medal_by_category);
            }
            // end: total medal emas, perak, perunggu dari setiap kategori semua klub

            // start: total medal emas, perak, perunggu secara keseluruhan dari semua klub
            $array_of_total_medal_by_category_all_club = [];
            $total_medal_by_category_gold = 0;
            $total_medal_by_category_silver = 0;
            $total_medal_by_category_bronze = 0;
            for ($k = 0; $k < count($result); $k++) {
                $total_medal_by_category_gold += $result[$k]['total_gold'];
                $total_medal_by_category_silver += $result[$k]['total_silver'];
                $total_medal_by_category_bronze += $result[$k]['total_bronze'];
            }
            $array_of_total_medal_by_category_all_club = [
                'gold' => $total_medal_by_category_gold,
                'silver' => $total_medal_by_category_silver,
                'bronze' => $total_medal_by_category_bronze
            ];
            // end: total medal emas, perak, perunggu secara keseluruhan dari semua klub 

            $response = [
                'title_header' => $title_header,
                'datatable' => $result,
                'total_medal_by_category' => $array_of_total_medal_by_category,
                'total_medal_by_category_all_club' => $array_of_total_medal_by_category_all_club
            ];

            return $response;
        } else {
            return [];
        }
    }


    protected function getDataEliminationTeam($category_detail_id)
    {
        $elimination_group = ArcheryEventEliminationGroup::where('category_id', $category_detail_id)->first();
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
