<?php

namespace App\BLoC\Web\ArcheryReport;

use DAI\Utils\Abstracts\Retrieval;
use App\Libraries\ClubRanked;
use App\Models\ArcheryClub;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryMasterTeamCategory;
use App\Models\ArcheryScoring;
use DAI\Utils\Exceptions\BLoCException;
use App\Exports\ClubRankReport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class GetArcheryReportClubRanked extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get("event_id");
        $data = ClubRanked::getEventRanked($event_id);

        // return $data;

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
            // return $d;
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
                // return $detail_club_with_medal_response;
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

        $coun_modal_array = count($result[0]["medal_array"]);
        $array_of_total_medal_by_category = [];

        for ($i = 0; $i < $coun_modal_array; $i++) {
            $total_medal_by_category = 0;
            foreach ($result as $key1 => $value1) {
                $total_medal_by_category += $value1["medal_array"][$i];
            }
            $array_of_total_medal_by_category[] = $total_medal_by_category;
        }

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

        $file_name = "CLUB_RANK_" . $event_id . '_' . date("YmdHis");
        $final_doc = '/club_rank/' . $event_id . '/' . $file_name . '.xlsx';

        $data = [
            'title_header' => $title_header,
            'datatable' => $result,
            'array_of_total_medal_by_category' => $array_of_total_medal_by_category,
            'array_of_total_medal_by_category_all_club' => $array_of_total_medal_by_category_all_club
        ];

        $excel = new ClubRankReport($data);
        $download = Excel::store($excel, $final_doc, 'public');

        $destinationPath = Storage::url($final_doc);
        $file_path = env('STOREG_PUBLIC_DOMAIN') . $destinationPath;
        return $file_path;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required'
        ];
    }
}
