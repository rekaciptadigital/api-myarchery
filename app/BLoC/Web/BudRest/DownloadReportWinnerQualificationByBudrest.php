<?php

namespace App\BLoC\Web\BudRest;

use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryEvent;
use PDFv2;
use Illuminate\Support\Facades\Redis;
use App\Models\ArcheryScoring;
use App\Models\BudRest;
use Illuminate\Support\Carbon;

class DownloadReportWinnerQualificationByBudrest extends Retrieval
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
        if (!$archery_event) {
            throw new BLoCException("event tidak terdaftar");
        }
        $logo_event = $archery_event->logo;

        $event_name_report = $archery_event->event_name;
        $start_date_event = dateFormatTranslate(Carbon::parse($archery_event->event_start_datetime)->format('d-F-Y'), false);
        $end_date_event = dateFormatTranslate(Carbon::parse($archery_event->event_end_datetime)->format('d-F-Y'), false);
        $event_date_report = $start_date_event . ' - ' . $end_date_event;
        $event_location_report = $archery_event->location;

        $competition_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct competition_category_id as competition_category'))
            ->where("event_id", $event_id)
            ->orderBy('competition_category_id', 'DESC')
            ->get();

        if ($competition_category->count() == 0) {
            throw new BLoCException("tidak ada data kategori terdaftar untuk event tersebut");
        }

        // ------------------------------------------ PRINT COVER ------------------------------------------ //
        $logo_archery_cover = '<img src="https://api-staging.myarchery.id/new-logo-archery.png" alt="" width="60%"></img>';
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


        foreach ($competition_category as $competition) {
            $age_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct age_category_id as age_category'))
                ->where("event_id", $event_id)
                ->where("competition_category_id", $competition->competition_category)
                ->orderBy('competition_category_id', 'DESC')
                ->get();

            if ($age_category->count() == 0) {
                throw new BLoCException("tidak ada data age category terdaftar untuk event tersebut");
            }

            foreach ($age_category as $age) {
                $distance_category = ArcheryEventCategoryDetail::select(DB::RAW('distinct distance_id as distance_category'))
                    ->where("event_id", $event_id)
                    ->where("competition_category_id", $competition->competition_category)
                    ->where("age_category_id", $age->age_category)
                    ->orderBy('competition_category_id', 'DESC')
                    ->get();

                if ($distance_category->count() == 0) {
                    throw new BLoCException("tidak ada data distance category terdaftar untuk event tersebut");
                }


                foreach ($distance_category as $distance) {
                    $team_category = ArcheryEventCategoryDetail::select(DB::RAW('team_category_id as team_category'), DB::RAW('archery_event_category_details.id as category_detail_id'))
                        ->where("event_id", $event_id)
                        ->where("competition_category_id", $competition->competition_category)
                        ->where("age_category_id", $age->age_category)
                        ->where("distance_id", $distance->distance_category)
                        ->leftJoin("archery_master_team_categories", 'archery_master_team_categories.id', 'archery_event_category_details.team_category_id')
                        ->orderBy("archery_master_team_categories.short", "ASC")
                        ->get();
                    if ($team_category->count() == 0) {
                        throw new BLoCException("tidak ada data team category terdaftar untuk event tersebut");
                    }


                    foreach ($team_category as $team) {
                        $category_detail = ArcheryEventCategoryDetail::find($team->category_detail_id);
                        if (!$category_detail) {
                            throw new BLoCException("category not found");
                        }

                        $config_budrest_category = BudRest::where("archery_event_category_id", $category_detail->id)->first();
                        if (!$config_budrest_category) {
                            continue;
                        }
                        $sessions = $category_detail->getArraySessionCategory();
                        $label_category = $category_detail->label_category;

                        $list_member_rank_with_point_score_qualification = ArcheryScoring::getScoringRankByCategoryId($category_detail->id, 1, $sessions, false, null, false, 1);

                        // generate slot
                        $winer_by_budrest = [];
                        for ($i = $config_budrest_category->bud_rest_start; $i <= $config_budrest_category->bud_rest_end; $i++) {
                            foreach ($list_member_rank_with_point_score_qualification as $value) {
                                if ($value["member"]["bud_rest_number"] == $i) {
                                    $winer_by_budrest[$i] = $value;
                                    break;
                                }
                            }
                        }

                        $pages[] = view('report_winner_qualification_by_budrest/report_per_category', [
                            'logo_event' => $logo_event,
                            'competition' => $competition->competition_category,
                            'logo_archery' => $logo_archery,
                            'event_name_report' => $event_name_report,
                            'event_date_report' => $event_date_report,
                            'event_location_report' => $event_location_report,
                            'label_category' => $label_category,
                            'data' => $winer_by_budrest,
                        ]);
                    }
                }
            }
        }

        $pdf = PDFv2::loadView('report_winner_qualification_by_budrest/all', ['pages' => $pages]);
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
        $fileName   = 'report_winner_by_budrest_' . $event_id . time() . '.pdf';
        // $fileName   = 'report_result_' . rand(pow(10, $digits - 1), pow(10, $digits) - 1) . '.pdf';
        $path = 'asset/report_winner_by_budrest';
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
