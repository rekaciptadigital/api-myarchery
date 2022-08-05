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
use Illuminate\Support\Carbon;

class ReportMedalClub extends Retrieval
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
        $logo_event = '<img src="' . Storage::disk('public')->path('logo/logo-event-series-2.png') . '" alt="" width="80%"></img>';
        $logo_archery = '<img src="' . Storage::disk('public')->path("logo/logo-archery.png") . '" alt="" width="80%"></img>';

        $archery_event = ArcheryEvent::find($event_id);
        if (!$archery_event) throw new BLoCException("event tidak terdaftar");

        $event_name_report = $archery_event->event_name;
        $start_date_event = dateFormatTranslate(Carbon::parse($archery_event->event_start_datetime)->format('d-F-Y'), false);
        $end_date_event = dateFormatTranslate(Carbon::parse($archery_event->event_end_datetime)->format('d-F-Y'), false);
        $event_date_report = $start_date_event . ' - ' . $end_date_event;
        $event_location_report = $archery_event->location;

        $competition_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct competition_category_id as competition_category'))->where("event_id", $event_id)
            ->orderBy('competition_category_id', 'DESC')->get();

        if (!$competition_category) throw new BLoCException("tidak ada data kategori terdaftar untuk event tersebut");

        // ------------------------------------------ PRINT COVER ------------------------------------------ //
        $logo_event_cover = '<img src="' . Storage::disk('public')->path("logo/logo-event-series-2.png") . '" alt="" width="25%"></img>';
        $logo_archery_cover = '<img src="' . Storage::disk('public')->path("logo/logo-archery.png") . '" alt="" width="60%"></img>';
        $cover_page = view('report_result/cover', [
            'cover_event' => $logo_event_cover,
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
        $data_medal_standing = $this->getMedalStanding($event_id);
        // return $data_medal_standing['title_header']['category']; die;
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
        // ------------------------------------------ END PRINT MEDAL STANDING ------------------------------------------ //


        $pdf = PDFv2::loadView('report_medal_club/all', ['pages' => $pages]);
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
            'footer-html' => $footer_html,
            'toc' => true,
            'toc-level-indentation' => '2rem',
            'enable-toc-back-links' => true,
        ]);

        $digits = 3;
        $fileName   = 'report_medal_club' . date("YmdHis") . '.pdf';
        // $fileName   = 'report_result_' . rand(pow(10, $digits - 1), pow(10, $digits) - 1) . '.pdf';
        $path = 'asset/report-report_medal_club';
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

    protected function getMedalStanding($event_id)
    {
        $data = ClubRanked::getEventRanked($event_id);

        $title_header = array();
        $competition_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct competition_category_id as competition_category'))->where("event_id", $event_id)
            ->orderBy('competition_category_id', 'DESC')->get();

        foreach ($competition_category as $competition) {
            $age_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct age_category_id as age_category'))->where("event_id", $event_id)
                ->where("competition_category_id", $competition->competition_category)
                ->orderBy('competition_category_id', 'DESC')->get();

            foreach ($age_category as $age) {
                $title_header['category'][$competition->competition_category]['age_category'][$age->age_category] = [
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
    }

}
