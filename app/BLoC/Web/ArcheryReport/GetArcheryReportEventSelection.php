<?php

namespace App\BLoC\Web\ArcheryReport;

use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use App\Models\ArcheryEvent;
use App\Models\ArcheryScoring;
use App\Models\ParentClassificationMembers;
use PDFv2;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Carbon;

class GetArcheryReportEventSelection extends Retrieval
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

        $pages = array();
        $logo_archery = '<img src="https://api-staging.myarchery.id/new-logo-archery.png" alt="" width="70%"></img>';
        $archery_event = ArcheryEvent::find($event_id);
        if (!$archery_event) {
            throw new BLoCException("event tidak terdaftar");
        }

        $parent_classification = ParentClassificationMembers::find($archery_event->parent_classification);
        if (!$parent_classification) {
            throw new BLoCException("parent classification not found");
        }

        $logo_event = $archery_event->logo;

        $event_name_report = $archery_event->event_name;
        $start_date_event = dateFormatTranslate(Carbon::parse($archery_event->event_start_datetime)->format('d-F-Y'), false);
        $end_date_event = dateFormatTranslate(Carbon::parse($archery_event->event_end_datetime)->format('d-F-Y'), false);
        $event_date_report = $start_date_event . ' - ' . $end_date_event;
        $event_location_report = $archery_event->location;

        $event_category_details = ArcheryEventCategoryDetail::where("event_id", $event_id)
            ->get();

        if (!$event_category_details) {
            throw new BLoCException("tidak ada data kategori terdaftar untuk event tersebut");
        }

        // ------------------------------------------ PRINT HEADER ------------------------------------------ //
        // $header_html = view('reports/event_selection/header', [
        //     'logo_event' => $logo_event,
        //     'logo_archery' => $logo_archery,
        //     'event_name_report' => $event_name_report,
        //     'event_date_report' => $event_date_report,
        //     'event_location_report' => $event_location_report
        // ]);
        // ------------------------------------------ END PRINT HEADER ------------------------------------------ //

        // ------------------------------------------ QUALIFICATION ------------------------------------------ //
        $all_result_qualification = [];
        foreach ($event_category_details as $category_detail) {
            if ($category_detail->category_team == "Team") {
                continue;
            }

            $session_qualification = [];
            for ($i = 0; $i < $category_detail->session_in_qualification; $i++) {
                $session_qualification[] = $i + 1;
            }

            $data_qualification = ArcheryScoring::getScoringRankByCategoryId($category_detail->id, 3, $session_qualification, false, null, false, 0);
            if (sizeof($data_qualification) == 0) {
                continue;
            }

            $qualification['category'] = $category_detail->label_category;
            $qualification['total_arrow'] = ($category_detail->count_stage * $category_detail->count_shot_in_stage) * $category_detail->session_in_qualification;
            $qualification['data'] = $data_qualification;
            $qualification["session_qualification"] = $category_detail->session_in_qualification;

            $all_result_qualification[] = $qualification;
        }

        $pages[] = view('reports/event_selection/qualification', [
            "parent_classification_title" => $parent_classification->title,
            'datas' => $all_result_qualification,
            'logo_event' => $logo_event,
            'logo_archery' => $logo_archery,
            'event_name_report' => $event_name_report,
            'event_date_report' => $event_date_report,
            'event_location_report' => $event_location_report
        ]);
        // ------------------------------------------ END QUALIFICATION ------------------------------------------ //


        // ------------------------------------------ ELIMINATION ------------------------------------------ //
        $all_result_elimination = [];
        foreach ($event_category_details as $category_detail) {
            if ($category_detail->category_team == "Team") continue;

            $session_elimination = [];
            for ($i = 0; $i < $category_detail->session_in_elimination_selection; $i++) {
                $session_elimination[] = $i + 1;
            }

            $data_elimination = ArcheryScoring::getScoringRankByCategoryIdForEliminationSelection($category_detail->id, 4, $session_elimination, true, null, false);
            if (sizeof($data_elimination) == 0) {
                continue;
            }

            $elimination['category'] = $category_detail->label_category;

            $elimination['total_arrow'] = ($category_detail->count_shoot_elimination_selection * $category_detail->session_in_elimination_selection) * $category_detail->session_in_elimination_selection;
            $elimination['data'] = $data_elimination;
            $elimination["session_in_elimination_selection"] = $category_detail->session_in_elimination_selection;

            $all_result_elimination[] = $elimination;
        }

        $pages[] = view('reports/event_selection/elimination', [
            "parent_classification_title" => $parent_classification->title,
            'datas' => $all_result_elimination,
            'logo_event' => $logo_event,
            'logo_archery' => $logo_archery,
            'event_name_report' => $event_name_report,
            'event_date_report' => $event_date_report,
            'event_location_report' => $event_location_report
        ]);
        // ------------------------------------------ END ELIMINATION ------------------------------------------ //


        // ------------------------------------------ ALL RESULT TOTAL IRAT ------------------------------------------ //
        $all_result_total_irat = [];
        foreach ($event_category_details as $category_detail) {
            if ($category_detail->category_team == "Team") continue;

            $session_qualification = [];
            for ($i = 0; $i < $category_detail->session_in_qualification; $i++) {
                $session_qualification[] = $i + 1;
            }

            $session_elimination = [];
            for ($i = 0; $i < $category_detail->session_in_elimination_selection; $i++) {
                $session_elimination[] = $i + 1;
            }

            $data_all_result = ArcheryScoring::getScoringRankByCategoryIdForEventSelection($category_detail->id, $session_qualification, $session_elimination, null);
            if (sizeof($data_all_result) == 0) {
                continue;
            }

            $all_result['category'] = $category_detail->label_category;
            $all_result['data'] = $data_all_result;

            $all_result_total_irat[] = $all_result;
        }

        $pages[] = view('reports/event_selection/all_result_total_irat', [
            "parent_classification_title" => $parent_classification->title,
            'datas' => $all_result_total_irat,
            'logo_event' => $logo_event,
            'logo_archery' => $logo_archery,
            'event_name_report' => $event_name_report,
            'event_date_report' => $event_date_report,
            'event_location_report' => $event_location_report
        ]);
        // ------------------------------------------ END ALL RESULT TOTAL IRAT ------------------------------------------ //

        $pdf = PDFv2::loadView('reports/event_selection/all', ['pages' => $pages]);
        $pdf->setOptions([
            'margin-top'    => 8,
            'margin-bottom' => 12,
            'page-size'     => 'a4',
            'orientation'   => 'landscape',
            'enable-javascript' => true,
            // 'header-html' => $header_html,
            'javascript-delay' => 9000,
            'no-stop-slow-scripts' => true,
            'enable-smart-shrinking' => true,
            'images' => true,
        ]);

        $digits = 3;
        $fileName   = 'report_result_event_selection_' . date("YmdHis") . '.pdf';
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
            "event_id" => 'required|integer|exists:archery_events,id'
        ];
    }
}
