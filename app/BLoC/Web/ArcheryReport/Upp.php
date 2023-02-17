<?php

namespace App\BLoC\Web\ArcheryReport;

use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use App\Models\ArcheryEvent;
use Illuminate\Support\Facades\Storage;
use PDFv2;
use Illuminate\Support\Facades\Redis;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventQualificationTime;
use Illuminate\Support\Carbon;

class Upp extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {

        $today = date("Y-m-d");
        $event_id = $parameters->get('event_id');
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
        $list_category_with_day = ArcheryEventQualificationTime::getCategoryByDate($event_id);

        // ------------------------------------------ PRINT COVER ------------------------------------------ //
        $logo_archery_cover = '<img src="' . Storage::disk('public')->path("logo/logo-archery.png") . '" alt="" width="60%"></img>';
        $cover_page = view('upp/cover', [
            'cover_event' => $logo_event,
            'logo_archery' => $logo_archery_cover,
            'event_name_report' => $event_name_report,
            'event_date_report' => $event_date_report,
            'event_location_report' => $event_location_report
        ]);
        // ------------------------------------------ END PRINT COVER ---------------------------
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
        foreach ($list_category_with_day as $key1 => $value1) {
            $data_all_category_in_day = [];
            foreach ($value1["category"] as $key2 => $value2) {
                $category_detail = ArcheryEventCategoryDetail::find($value2->id);
                $category_team_type = $value2->getCategoryType();

                $data_qualification = ArcheryEventParticipant::getQualification($category_detail); // daptakan data kualifikasi individu dan beregu
                $data_report_qualification_individu = ArcheryEventParticipant::getData($category_detail->id, "qualification", $event_id);
                $data_report_by_team_qualification_individu = [];

                // ====================== qualification ==========================
                if (strtolower($category_team_type) == "individual") {
                    if (!empty($data_report_qualification_individu[0])) {
                        $data_report_by_team_qualification_individu["team"] = "individual";
                        $data_report_by_team_qualification_individu["data"] = $data_report_qualification_individu;
                        $data_report_by_team_qualification_individu["type"] = "qualification";
                        $data_all_category_in_day[] = $data_report_by_team_qualification_individu;
                    }
                }

                $data_elimination_team = ArcheryEventParticipant::getDataEliminationTeam($category_detail->id);
                $data_report_by_team_qualification_team = [];
                if (strtolower($category_team_type) == "team") {
                    if ($data_elimination_team == []) {
                        // start blok : daptkan juara 1 2 3 kualifikasi beregu
                        $new_data_qualification_best_of_three = [];
                        foreach ($data_qualification as $dq) {
                            $new_data_qualification_best_of_three[] = $dq;
                            if (count($new_data_qualification_best_of_three) == 3) {
                                break;
                            }
                        }
                        // end blok : daptkan juara 1 2 3 kualifikasi beregu
                        $data_report_by_team_qualification_team["team"] = "team";
                        $data_report_by_team_qualification_team["data"] = $new_data_qualification_best_of_three;
                        $data_report_by_team_qualification_team["category_label"] = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id);
                        $data_report_by_team_qualification_team["type"] = "qualification";
                        $data_all_category_in_day[] = $data_report_by_team_qualification_team;
                    }
                }
                // ================================ end qualification ==========================

                // ================================ elimination ==================================
                $data_report_by_team_elimination_individu = [];
                $data_report_elimination_individu = ArcheryEventParticipant::getData($category_detail->id, "elimination", $event_id);
                if (strtolower($category_team_type) == "individual") {
                    if (!empty($data_report_elimination_individu[0])) {
                        $data_report_by_team_elimination_individu["team"] = "individual";
                        $data_report_by_team_elimination_individu["data"] = $data_report_elimination_individu;
                        $data_report_by_team_elimination_individu["type"] = "elimination";
                        $data_report_by_team_elimination_individu["category_label"] = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id);
                        $data_all_category_in_day[] = $data_report_by_team_elimination_individu;
                    }
                }

                $data_report_by_team_elimination_team = [];
                if (strtolower($category_team_type) == "team") {
                    $data_elimination_team = ArcheryEventParticipant::getDataEliminationTeam($category_detail->id);
                    if (!empty($data_elimination_team)) {
                        $data_report_by_team_elimination_team["team"] = "team";
                        $data_report_by_team_elimination_team["data"] = $data_elimination_team;
                        $data_report_by_team_elimination_team["type"] = "elimination";
                        $data_report_by_team_elimination_team["category_label"] = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id);
                        $data_all_category_in_day[] = $data_report_by_team_elimination_team;
                    }
                }

                // ================================end elimination ===============================


            }
            $pages[] = view('upp/data', [
                "with_contingent" => $archery_event->with_contingent,
                'data_report' => $data_all_category_in_day,
                'logo_event' => $logo_event,
                'logo_archery' => $logo_archery,
                'event_name_report' => $event_name_report,
                'event_date_report' => $event_date_report,
                'event_location_report' => $event_location_report,
                'day' => $value1["day"]
            ]);
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

        $fileName   = 'upp_' . $event_id . "_" . time() . '.pdf';
        // $fileName   = 'report_result_' . rand(pow(10, $digits - 1), pow(10, $digits) - 1) . '.pdf';
        $path = 'asset/upp';
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
