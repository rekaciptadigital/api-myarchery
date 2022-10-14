<?php

namespace App\BLoC\Web\ArcheryReport;

use App\Libraries\EliminationFormatPDF;
use App\Libraries\EliminationFormatPDFV2;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryMasterCompetitionCategory;
use App\Models\ArcheryMasterTeamCategory;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use PDFv2;
use Illuminate\Support\Facades\Redis;

class ReportRankQualification extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get("event_id");
        $event = ArcheryEvent::find($event_id);

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
            $data_elimination = ArcheryEventParticipant::getTemplateIndividu($category);
        }

        $type = 'Elimination';
        $report = 'Result';
        $data_report = ArcheryEventParticipant::getData($category->id, $type, $event_id);


        if (strtolower($team_category->type) == "individual") {
            if (!empty($data_report[0])) {

                $elimination_individu = ArcheryEventElimination::where("event_category_id", $category_detail->id)->first();
                $data_graph = EliminationFormatPDF::getDataGraph($data_report[1]);

                if ($data_elimination['updated'] == false) {
                    if ($elimination_individu->count_participant == 32) {
                        $data_graph_individu = EliminationFormatPDFV2::getViewDataGraphIndividuOfBigTwentyTwo($data_elimination);
                        $view_path = 'report_result/elimination_graph/individu/graph_thirtytwo';
                        $title_category = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_id);
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
            if ($data_elimination['updated'] == false) {
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

        $pdf = PDFv2::loadView('qualification-rank', [
            'data' => $list_scoring_qualification,
            "logo_event" => $logo_event,
            "event_location_report" => $event_location_report,
            "logo_archery" => $logo_archery,
            "event_name_report" => $event_name_report,
            "event_date_report" => $event_date_report,
            "competition" => $competition->label,
            "category" => $category->label_category
        ]);
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

        $fileName   = 'qualification_rank_' . $category_id . "_" . time() . '.pdf';

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
