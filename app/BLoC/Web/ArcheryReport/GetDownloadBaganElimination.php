<?php

namespace App\BLoC\Web\ArcheryReport;

use App\Libraries\EliminationFormatPDF;
use App\Libraries\EliminationFormatPDFV2;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryMasterCompetitionCategory;
use App\Models\ArcheryMasterTeamCategory;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Carbon;
use PDFv2;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class GetDownloadBaganElimination extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get("event_id");
        $event = ArcheryEvent::find($event_id);

        $event_name_report = $event->event_name;
        $start_date_event = dateFormatTranslate(Carbon::parse($event->event_start_datetime)->format('d-F-Y'), false);
        $end_date_event = dateFormatTranslate(Carbon::parse($event->event_end_datetime)->format('d-F-Y'), false);
        $event_date_report = $start_date_event . ' - ' . $end_date_event;
        $event_location_report = $event->location;
        $logo_event = $event->logo;
        $logo_archery = '<img src="https://api-staging.myarchery.id/new-logo-archery.png" alt="" width="80%"></img>';

        $category_id = $parameters->get("category_id");
        $category = ArcheryEventCategoryDetail::find($category_id);

        $team_category = ArcheryMasterTeamCategory::find($category->team_category_id);
        if (!$team_category) {
            throw new BLoCException("team category not found");
        }

        $competition = ArcheryMasterCompetitionCategory::find($category->competition_category_id);
        if (!$competition) {
            throw new BLoCException("competition not found");
        }

        if (strtolower($team_category->type) == "team") {
            $data_elimination = ArcheryEventParticipant::getTemplateTeam($category);
        } else {
            $data_elimination = ArcheryEventParticipant::getTemplateIndividu($category, $event);
        }

        $type = 'Elimination';
        $report = 'Result';
        $data_report = ArcheryEventParticipant::getData($category->id, $type, $event_id);


        if (strtolower($team_category->type) == "individual") {
            if (!empty($data_report[0])) {

                $elimination_individu = ArcheryEventElimination::where("event_category_id", $category_id)->first();
                $data_graph = EliminationFormatPDF::getDataGraph($data_report[1], $event);

                if ($data_elimination['updated'] == false) {
                    if ($elimination_individu->count_participant == 32) {
                        $data_graph_individu = EliminationFormatPDFV2::getViewDataGraphIndividuOfBigTwentyTwo($data_elimination);
                        $view_path = 'report_result/elimination_graph/individu/graph_thirtytwo';
                        $title_category = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_id);
                        $view = EliminationFormatPDFV2::renderPageGraphIndividuOfBigTwentyTwo($view_path, $data_graph_individu, $competition->competition_category, $title_category, $logo_event, $logo_archery, $event_name_report, $event_location_report, $event_date_report);
                    } else if ($elimination_individu->count_participant == 16) {
                        $data_graph_individu = EliminationFormatPDFV2::getViewDataGraphIndividuOfBigSixteen($data_elimination);
                        $view_path = 'report_result/elimination_graph/individu/graph_sixteen';
                        $title_category = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_id);
                        $view = EliminationFormatPDFV2::renderPageGraphIndividuOfBigSixteen($view_path, $data_graph_individu, $competition->competition_category, $title_category, $logo_event, $logo_archery, $event_name_report, $event_location_report, $event_date_report);
                    } else if ($elimination_individu->count_participant == 8) {
                        if ($data_graph) {
                            $data = EliminationFormatPDF::getViewDataGraph8($data_graph);
                            $view_path = 'report_result/graph_eight';
                            $view = EliminationFormatPDF::renderPageGraph8_reportEvent($view_path, $data, $report, $data_report, $logo_event, $logo_archery, $competition, $event_name_report, $event_location_report, $event_date_report);
                        }
                    } elseif ($elimination_individu->count_participant == 4) {
                        if ($data_graph) {
                            $data = EliminationFormatPDF::getViewDataGraph4($data_graph);
                            $view_path = 'report_result/graph_four';
                            $view = EliminationFormatPDF::renderPageGraph4_reportEvent($view_path, $data, $report, $data_report, $logo_event, $logo_archery, $competition, $event_name_report, $event_location_report, $event_date_report);
                        }
                    }
                }
            }
        }

        if (strtolower($team_category->type) == "team") {
            $elimination_team = ArcheryEventEliminationGroup::where("category_id", $category->id)->first();

            //print bagan eliminasi
            if ($data_elimination['updated'] == false) {
                if ($elimination_team->count_participant == 4) {
                    $data_graph_team = EliminationFormatPDFV2::getViewDataGraphTeamOfBigFour($data_elimination);
                    $view_path = 'report_result/elimination_graph/team/graph_four';
                    $title_category = ArcheryEventCategoryDetail::getCategoryLabelComplete($category->id);
                    $view = EliminationFormatPDFV2::renderPageGraphTeamOfBigFour($view_path, $data_graph_team, $competition->competition_category, $title_category, $logo_event, $logo_archery, $event_name_report, $event_location_report, $event_date_report);
                } else if ($elimination_team->count_participant == 8) {
                    $data_graph_team = EliminationFormatPDFV2::getViewDataGraphTeamOfBigEight($data_elimination);
                    $view_path = 'report_result/elimination_graph/team/graph_eight';
                    $title_category = ArcheryEventCategoryDetail::getCategoryLabelComplete($category->id);
                    $view = EliminationFormatPDFV2::renderPageGraphTeamOfBigEight($view_path, $data_graph_team, $competition->competition_category, $title_category, $logo_event, $logo_archery, $event_name_report, $event_location_report, $event_date_report);
                }
            }
            //end print bagan eliminasi
        }

        if (!isset($view)) {
            throw new BLoCException("data eliminasi tidak ada");
        }

        $pdf = PDFv2::loadHtml($view);
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
            // 'header-html' => $header_html,
            // 'footer-html' => $footer_html,
            // 'toc' => true,
            // 'toc-level-indentation' => '2rem',
            // 'enable-toc-back-links' => true,
        ]);

        $fileName   = 'elimination_graph' . $category_id . "_" . time() . '.pdf';

        $path = 'asset/qualification_rank';
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
            "event_id" => 'required|integer|exists:archery_events,id',
            "category_id" => "required|integer|exists:archery_event_category_details,id"
        ];
    }
}
