<?php

namespace App\BLoC\Web\ArcheryReport;

use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryEvent;
use App\Models\ArcheryScoring;
use PDFv2;
use Illuminate\Support\Facades\Redis;
use App\Models\ArcheryMasterCompetitionCategory;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Carbon;

class ReportRankQualification extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get('event_id');
        $category_id = $parameters->get("category_id");

        $logo_archery = '<img src="https://api-staging.myarchery.id/new-logo-archery.png" alt="" width="80%"></img>';

        $archery_event = ArcheryEvent::find($event_id);

        $logo_event = $archery_event->logo;

        $category = ArcheryEventCategoryDetail::find($category_id);

        $competition = ArcheryMasterCompetitionCategory::find($category->competition_category_id);

        $sessions = $category->getArraySessionCategory();

        $event_name_report = $archery_event->event_name;
        $start_date_event = dateFormatTranslate(Carbon::parse($archery_event->event_start_datetime)->format('d-F-Y'), false);
        $end_date_event = dateFormatTranslate(Carbon::parse($archery_event->event_end_datetime)->format('d-F-Y'), false);
        $event_date_report = $start_date_event . ' - ' . $end_date_event;
        $event_location_report = $archery_event->location;

        $list_scoring_qualification = ArcheryScoring::getScoringRankByCategoryId($category_id, 1,  $sessions, false, null, false, 1);
        if (count($list_scoring_qualification) == 0) {
            throw new BLoCException("data skoring kosong");
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
