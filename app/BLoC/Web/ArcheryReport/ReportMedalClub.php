<?php

namespace App\BLoC\Web\ArcheryReport;

use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryEvent;
use PDFv2;
use Illuminate\Support\Facades\Redis;
use App\Models\ArcheryEventParticipant;
use Illuminate\Support\Carbon;

class ReportMedalClub extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {

        $event_id = $parameters->get('event_id');

        $pages = array();
        $logo_archery = '<img src="https://api-staging.myarchery.id/new-logo-archery.png" alt="" width="80%"></img>';

        $archery_event = ArcheryEvent::find($event_id);
        if (!$archery_event) throw new BLoCException("event tidak terdaftar");

        $parent_classifification_id = $archery_event->parent_classification;

        $event_name_report = $archery_event->event_name;
        $start_date_event = dateFormatTranslate(Carbon::parse($archery_event->event_start_datetime)->format('d-F-Y'), false);
        $end_date_event = dateFormatTranslate(Carbon::parse($archery_event->event_end_datetime)->format('d-F-Y'), false);
        $event_date_report = $start_date_event . ' - ' . $end_date_event;
        $event_location_report = $archery_event->location;

        $competition_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct competition_category_id as competition_category'))
            ->where("event_id", $event_id)
            ->orderBy('competition_category_id', 'DESC')
            ->get();

        if (!$competition_category) {
            throw new BLoCException("tidak ada data kategori terdaftar untuk event tersebut");
        }

        // ------------------------------------------ PRINT COVER ------------------------------------------ //
        $logo_event = $archery_event->logo;
        $logo_archery_cover = '<img src="https://api-staging.myarchery.id/new-logo-archery.png" alt="" width="60%"></img>';
        $cover_page = view('report_medal_club/cover', [
            'cover_event' => $logo_event,
            'logo_archery' => $logo_archery_cover,
            'event_name_report' => $event_name_report,
            'event_date_report' => $event_date_report,
            'event_location_report' => $event_location_report
        ]);
        // ------------------------------------------ END PRINT COVER ------------------------------------------ //


        // ------------------------------------------ PRINT FOOTER ------------------------------------------ //
        // $footer_html = view('report_result/footer');
        // ------------------------------------------ END PRINT FOOTER ------------------------------------------ //


        // ------------------------------------------ PRINT MEDAL STANDING ------------------------------------------ //
        $data_medal_standing = ArcheryEventParticipant::getMedalStanding($event_id);

        if ($data_medal_standing != [] && count($data_medal_standing["datatable"]) > 0) {
            $pages[] = view('report_result/club_rank_medals_standing', [
                "with_contingent" => $archery_event->with_contingent,
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
            // ------------------------------------------ END PRINT MEDAL STANDING ------------------------------------------ //



            // =============================== data ======================================
            foreach ($data_medal_standing['datatable'] as $key => $dms) {                
                $pages[] = view('report_medal_club/dataTable', [
                    "club_name" => $dms["club_name"],
                    "country_name" => $dms["country_name"],
                    "province_name" => $dms["province_name"],
                    "city_name" => $dms["city_name"],
                    "children_classification_members_name" => $dms["children_classification_members_name"],
                    "parent_classification_type" => $parent_classifification_id,
                    'logo_event' => $logo_event,
                    "dms" => $dms,
                    'logo_archery' => $logo_archery,
                    'event_name_report' => $event_name_report,
                    'event_date_report' => $event_date_report,
                    'event_location_report' => $event_location_report,
                    'headers' => $data_medal_standing['title_header']['category'],
                    "rank" => $key + 1,
                    "category" => $data_medal_standing["title_header"]["category"],
                    "total_gold" => $dms["total_gold"],
                    "total_silver" => $dms["total_silver"],
                    "total_bronze" => $dms["total_bronze"],
                    'total_medal_by_category' => $data_medal_standing['total_medal_by_category'],
                    'total_medal_by_category_all_club' => $data_medal_standing['total_medal_by_category_all_club']
                ]);
            }
        }
        // =============================== enddata ===================================

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
        $fileName   = 'report_result_medal_club_' . $event_id . "_" . time() . '.pdf';
        // $fileName   = 'report_result_' . rand(pow(10, $digits - 1), pow(10, $digits) - 1) . '.pdf';
        $path = 'asset/report_result_medal_club';
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
}
