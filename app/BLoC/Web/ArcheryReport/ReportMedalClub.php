<?php

namespace App\BLoC\Web\ArcheryReport;

use App\Libraries\ClubRanked;
use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryEvent;
use PDFv2;
use Illuminate\Support\Facades\Redis;
use App\Models\ArcheryEventParticipant;
use App\Models\ParentClassificationMembers;
use App\Models\UrlReport;
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

        $check_is_exist_report = UrlReport::where("event_id", $event_id)->where("type", "medal_recapitulation")->first();
        if ($check_is_exist_report) {
            return [
                "file_path" => $check_is_exist_report->url
            ];
        }

        $parent_classifification_id = $archery_event->parent_classification;

        $event_name_report = $archery_event->event_name;
        $start_date_event = dateFormatTranslate(Carbon::parse($archery_event->event_start_datetime)->format('d-F-Y'), false);
        $end_date_event = dateFormatTranslate(Carbon::parse($archery_event->event_end_datetime)->format('d-F-Y'), false);
        $event_date_report = $start_date_event . ' - ' . $end_date_event;
        $event_location_report = $archery_event->location;

        $parent_classifification_id = $archery_event->parent_classification;
        $parent_classification_member = ParentClassificationMembers::find($parent_classifification_id);
        if (!$parent_classification_member) {
            throw new BLoCException("parent_classification_members not found");
        }

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

        $data_medal_standing_2 = ClubRanked::getEventRanked($event_id, 1);

        // ------------------------------------------ PRINT MEDAL STANDING 1 ------------------------------------------ //
        $data_medal_standing = ArcheryEventParticipant::getMedalStanding($event_id, $data_medal_standing_2);
        if (count($data_medal_standing) > 0) {
            $pages[] = view('report_medal_club/club_rank_medals_standing', [
                'logo_event' => $logo_event,
                'logo_archery' => $logo_archery,
                'event_name_report' => $event_name_report,
                'event_date_report' => $event_date_report,
                'event_location_report' => $event_location_report,
                'headers' => $data_medal_standing['title_header']['category'],
                'datatables' => $data_medal_standing['datatable'],
                'total_medal_by_category' => $data_medal_standing['total_medal_by_category'],
                'total_medal_by_category_all_club' => $data_medal_standing['total_medal_by_category_all_club'],
                'parent_classification_member_title' => $parent_classification_member->title,
            ]);
        }
        // ------------------------------------------ END PRINT MEDAL STANDING ------------------------------------------ //

        // ========================================== PRINT MEDAL STANDING 2 ===============================================
        if (count($data_medal_standing_2) > 0) {
            $gold_individu = 0;
            $silver_individu = 0;
            $bronze_individu = 0;
            $total_medal_individu = 0;
            $gold_team = 0;
            $silver_team = 0;
            $bronze_team = 0;
            $total_medal_team = 0;
            $total_gold = 0;
            $total_silver = 0;
            $total_bronze = 0;
            $total_all = 0;
            $new_data = [];
            foreach ($data_medal_standing_2 as $key2 => $value_2) {
                if ($value_2["total"] == 0) {
                    continue;
                }
                $gold_individu += $value_2['detail_modal_by_group']['indiividu']['gold'];
                $silver_individu += $value_2['detail_modal_by_group']['indiividu']['silver'];
                $bronze_individu += $value_2['detail_modal_by_group']['indiividu']['bronze'];
                $total_medal_individu += $value_2['detail_modal_by_group']['indiividu']['total'];

                $gold_team += $value_2['detail_modal_by_group']['team']['gold'];
                $silver_team += $value_2['detail_modal_by_group']['team']['silver'];
                $bronze_team += $value_2['detail_modal_by_group']['team']['bronze'];
                $total_medal_team += $value_2['detail_modal_by_group']['team']['total'];

                $total_gold += $value_2['gold'];
                $total_silver += $value_2['silver'];
                $total_bronze += $value_2['bronze'];
                $total_all += $value_2['total'];

                $new_data[] = $value_2;
            }

            $pages[] = view('report_medal_club/club_rank_medals_standing_2', [
                'logo_event' => $logo_event,
                'logo_archery' => $logo_archery,
                'event_name_report' => $event_name_report,
                'event_date_report' => $event_date_report,
                'event_location_report' => $event_location_report,
                'datatables' => $new_data,
                'parent_classification_member_title' => $parent_classification_member->title,
                "gold_individu" => $gold_individu,
                "silver_individu" => $silver_individu,
                "bronze_individu" => $bronze_individu,
                "total_medal_individu" => $total_medal_individu,
                "gold_team" => $gold_team,
                "silver_team" => $silver_team,
                "bronze_team" => $bronze_team,
                "total_medal_team" => $total_medal_team,
                "total_gold" => $total_gold,
                "total_silver" => $total_silver,
                "total_bronze" => $total_bronze,
                "total_all" => $total_all
            ]);
        }
        // ========================================== END PRINT MEDAL STANDING 2 ==================================================


        // ============================================ PRINT MEDAL STANDING 3 ==================================================
        // ------------------------------------------ PRINT MEDAL STANDING 3 ------------------------------------------ //
        if (count($data_medal_standing_2) > 0) {
            $new_data = [];
            $title_header = array();
            $competition_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct competition_category_id as competition_category'))
                ->where("event_id", $event_id)
                ->orderBy('competition_category_id', 'DESC')
                ->get();

            foreach ($competition_category as $competition) {
                $title_header[$competition->competition_category]["qualification"] = [
                    'g' => null,
                    's' => null,
                    'b' => null,
                ];

                $title_header[$competition->competition_category]["elimination"] = [
                    'g' => null,
                    's' => null,
                    'b' => null,
                ];

                array_push($title_header[$competition->competition_category]);
            }

            $detail_total_medal_for_last_row = [];
            foreach ($title_header as $key_title_header => $value_title_header) {
                $detail_total_medal_for_last_row[$key_title_header] = [
                    "qualification" => [
                        "gold" => 0,
                        "silver" => 0,
                        "bronze" => 0,
                    ],
                    "elimination" => [
                        "gold" => 0,
                        "silver" => 0,
                        "bronze" => 0,
                    ]
                ];
                foreach ($data_medal_standing_2 as $key_data_medal_standing_2 => $value_data_medal_standing_2) {
                    foreach ($value_data_medal_standing_2["detail_medal"]["category"] as $key_value_data_medal_standing_2 => $value_value_data_medal_standing_2) {
                        if ($key_value_data_medal_standing_2 == $key_title_header) {
                            $detail_total_medal_for_last_row[$key_title_header]["qualification"]["gold"] += $value_value_data_medal_standing_2["qualification"]["gold"];
                            $detail_total_medal_for_last_row[$key_title_header]["qualification"]["silver"] += $value_value_data_medal_standing_2["qualification"]["silver"];
                            $detail_total_medal_for_last_row[$key_title_header]["qualification"]["bronze"] += $value_value_data_medal_standing_2["qualification"]["bronze"];

                            $detail_total_medal_for_last_row[$key_title_header]["elimination"]["gold"] += $value_value_data_medal_standing_2["elimination"]["gold"];
                            $detail_total_medal_for_last_row[$key_title_header]["elimination"]["silver"] += $value_value_data_medal_standing_2["elimination"]["silver"];
                            $detail_total_medal_for_last_row[$key_title_header]["elimination"]["bronze"] += $value_value_data_medal_standing_2["elimination"]["bronze"];
                        }
                    }
                }
            }

            $detail_club_with_medal_response = [];
            $detail_sum_medal_last_row = [
                "gold" => 0,
                "silver" => 0,
                "bronze" => 0,
            ];

            foreach ($data_medal_standing_2 as $key2 => $value_2) {
                if ($value_2["total"] == 0) {
                    continue;
                }

                $detail_club_with_medal_response["contingent_name"] = $value_2["contingent_name"];

                $total_all_gold = 0;
                $total_all_silver = 0;
                $total_all_bronze = 0;

                foreach ($competition_category as $competition) {
                    $gold_qualification = 0;
                    $silver_qualification = 0;
                    $bronze_qualification = 0;

                    $gold_elimination = 0;
                    $silver_elimination = 0;
                    $bronze_elimination = 0;


                    $gold_qualification += $value_2["detail_medal"]["category"][$competition->competition_category]["qualification"]["gold"];
                    $total_all_gold += $value_2["detail_medal"]["category"][$competition->competition_category]["qualification"]["gold"];

                    $silver_qualification += $value_2["detail_medal"]["category"][$competition->competition_category]["qualification"]["silver"];
                    $total_all_silver += $value_2["detail_medal"]["category"][$competition->competition_category]["qualification"]["silver"];

                    $bronze_qualification += $value_2["detail_medal"]["category"][$competition->competition_category]["qualification"]["bronze"];
                    $total_all_bronze += $value_2["detail_medal"]["category"][$competition->competition_category]["qualification"]["bronze"];

                    $gold_elimination += $value_2["detail_medal"]["category"][$competition->competition_category]["elimination"]["gold"];
                    $total_all_gold += $value_2["detail_medal"]["category"][$competition->competition_category]["elimination"]["gold"];

                    $silver_elimination += $value_2["detail_medal"]["category"][$competition->competition_category]["elimination"]["silver"];
                    $total_all_silver += $value_2["detail_medal"]["category"][$competition->competition_category]["elimination"]["silver"];

                    $bronze_elimination += $value_2["detail_medal"]["category"][$competition->competition_category]["elimination"]["bronze"];
                    $total_all_bronze += $value_2["detail_medal"]["category"][$competition->competition_category]["elimination"]["bronze"];


                    $detail_club_with_medal_response[$competition->competition_category]["qualification"] = [
                        "gold" => $gold_qualification,
                        "silver" => $silver_qualification,
                        "bronze" => $bronze_qualification
                    ];

                    $detail_club_with_medal_response[$competition->competition_category]["elimination"] = [
                        "gold" => $gold_elimination,
                        "silver" => $silver_elimination,
                        "bronze" => $bronze_elimination
                    ];
                }

                $detail_club_with_medal_response["total_all_gold"] = $total_all_gold;
                $detail_club_with_medal_response["total_all_silver"] = $total_all_silver;
                $detail_club_with_medal_response["total_all_bronze"] = $total_all_bronze;

                $detail_sum_medal_last_row["gold"] += $total_all_gold;
                $detail_sum_medal_last_row["silver"] += $total_all_silver;
                $detail_sum_medal_last_row["bronze"] += $total_all_bronze;

                $new_data[] = $detail_club_with_medal_response;
            }


            $pages[] = view('report_medal_club/club_rank_medal_standing_3', [
                'logo_event' => $logo_event,
                'logo_archery' => $logo_archery,
                'event_name_report' => $event_name_report,
                'event_date_report' => $event_date_report,
                'event_location_report' => $event_location_report,
                'datatables' => $new_data,
                'parent_classification_member_title' => $parent_classification_member->title,
                'title_header' => $title_header,
                "detail_sum_medal_last_row" => $detail_sum_medal_last_row,
                "detail_total_medal_for_last_row" => $detail_total_medal_for_last_row
            ]);
        }
        // ============================================ END PRINT MEDAL STANDING 3 ===============================================

        if ($data_medal_standing != [] && count($data_medal_standing["datatable"]) > 0) {
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
                    'total_medal_by_category_all_club' => $data_medal_standing['total_medal_by_category_all_club'],
                    'parent_classification_member_title' => $parent_classification_member->title,
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

        $fileName   = 'report_result_medal_club_' . $event_id . "_" . time() . '.pdf';
        $path = 'asset/report_result_medal_club';
        $pdf->save('' . $path . '/' . $fileName . '');
        $response = [
            'file_path' => url(env('APP_HOSTNAME') . $path . '/' . $fileName . '')
        ];

        // save url pdf db
        $url_report = new UrlReport();
        $url_report->url = $response["file_path"];
        $url_report->type = "medal_recapitulation";
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
}
