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
        foreach ($data as $key => $value) {
            // return $value; die;
            // return count($value['detail_medal']['category']); die;
            foreach ($value['detail_medal']['category'] as $key => $value2) {
                // return count($value2); die;

            }
        }

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
                'count_colspan' => count($age_category)*3
            ];
            array_push($title_header['category'][$competition->competition_category], $count_colspan);
        }

        // dapat list club yang joint event baik individu maupun beregu
        // $clubs = ArcheryClub::all();
        // $list_club_join_event = [];
        // foreach ($clubs as $key => $club) {
        //     $participant = ArcheryEventParticipant::where("event_id", $event_id)->where("status", 1)->where("club_id", $club->id)->first();
        //     if ($participant) {
        //         $list_club_join_event[] = $club;
        //     }
        // }

        // $category_events = ArcheryEventCategoryDetail::select('id', 'session_in_qualification', 'team_category_id', 'age_category_id', 'competition_category_id','distance_id')->where("event_id", $event_id)
        //     ->where("is_show", 1)
        //     ->get()
        //     ->groupBy(["competition_category_id", "age_category_id"]);

        // $list_club_with_medal = [];
        // foreach ($list_club_join_event as $key => $club) {
        //     $detail_club_with_medal = [];
        //     $detail_club_with_medal["club_name"] = $club->name;
        //     $total_gold_medal = 0;
        //     $total_silver_medal = 0;
        //     $total_bronze_medal = 0;
        //     $list_medal = [];
        //     foreach ($category_events as $key1 => $value1) {
        //         foreach ($value1 as $key2 => $value2) {
        //             foreach ($value2 as $key3 => $value3) {
        //                 $qualification_medal = $this->getClubMedalQualificationIndividualAndTeam($club->id, $value3);
        //                 $total_gold_medal = $total_gold_medal + $qualification_medal["gold"];
        //                 $total_silver_medal = $total_silver_medal + $qualification_medal["silver"];
        //                 $total_bronze_medal = $total_bronze_medal + $qualification_medal["bronze"];
        //             }
        //         }
        //     }

        //     $detail_club_with_medal["total_gold_medal"] = $total_gold_medal;
        //     $detail_club_with_medal["total_silver_medal"] = $total_silver_medal;
        //     $detail_club_with_medal["total_bronze_medal"] = $total_bronze_medal;
        //     array_push($list_club_with_medal, $detail_club_with_medal);
        // }

        // return $list_club_with_medal; die;

        $file_name = "CLUB_RANK_". $event_id .'_'.date("YmdHis");
        $final_doc = '/club_rank/'.$event_id.'/'.$file_name.'.xlsx';
    
        $data = [
            'title_header' => $title_header,
            'datatable' => null,
        ];

        $excel = new ClubRankReport($data);
        $download= Excel::store($excel, $final_doc, 'public');
       
        $destinationPath = Storage::url($final_doc);
        $file_path = env('STOREG_PUBLIC_DOMAIN').$destinationPath;
        return $file_path;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required'
        ];
    }

    private function getClubMedalQualificationIndividualAndTeam($club_id, $category)
    {
        $gold_medal = 0;
        $silver_medal = 0;
        $bronze_medal = 0;
        $session = [];
        for ($i = 0; $i < $category->session_in_qualification; $i++) {
            $session[] = $i + 1;
        }
        if ($category->categoryTeam == "Individual") {
            $member_rank = ArcheryScoring::getScoringRankByCategoryId($category, 1, $session, false, null, true);
            foreach ($member_rank as $key => $mr) {
                if ($mr["club_id"] == $club_id) {
                    if ($key + 1 == 1) {
                        $gold_medal = $gold_medal + 1;
                    }

                    if ($key + 1 == 2) {
                        $silver_medal = $silver_medal + 1;
                    }

                    if ($key + 1 == 3) {
                        $bronze_medal = $bronze_medal + 1;
                    }
                }
            }
        } else {
            $elimination_group = ArcheryEventEliminationGroup::where("category_id", $category->id);
            if (!$elimination_group) {
                if ($category->team_category_id == "mix_team") {
                    $mix_team_rank = ArcheryScoring::teamBestOfThree($category);
                    foreach ($mix_team_rank as $key_club => $mtr) {
                        if ($mtr["club_id"] == $club_id) {
                            if ($key_club + 1 == 1) {
                                $gold_medal = $gold_medal + 1;
                            }

                            if ($key_club + 1 == 2) {
                                $silver_medal = $silver_medal + 1;
                            }

                            if ($key_club + 1 == 3) {
                                $bronze_medal = $bronze_medal + 1;
                            }
                        }
                    }
                } else {
                    $team_category = ArcheryMasterTeamCategory::find($category->team_category_id);
                    if (!$team_category) {
                        throw new BLoCException("team category not found");
                    }
                    $team_cat = ($category->team_category_id) == "male_team" ? "individu male" : "individu female";
                    $category_detail_individu = ArcheryEventCategoryDetail::select('id', 'session_in_qualification')->where("event_id", $category->event_id)
                        ->where("age_category_id", $category->age_category_id)
                        ->where("competition_category_id", $category->competition_category_id)
                        ->where("distance_id", $category->distance_id)
                        ->where("team_category_id", $team_cat)
                        ->first();

                    if (!$category_detail_individu) {
                        throw new BLoCException("category individu tidak ditemukan");
                    }
                    $team = ArcheryScoring::mixTeamBestOfThree($category_detail_individu->id, $category_detail_individu->session_in_qualification, $category->id);
                    foreach ($team as $key_team => $t) {
                        if ($t["club_id"] == $club_id) {
                            if ($t + 1 == 1) {
                                $gold_medal = $gold_medal + 1;
                            }

                            if ($t + 1 == 2) {
                                $silver_medal = $silver_medal + 1;
                            }

                            if ($t + 1 == 3) {
                                $bronze_medal = $bronze_medal + 1;
                            }
                        }
                    }
                }
            }
        }

        return [
            "club_id" => $club_id,
            "gold" => $gold_medal,
            "silver" => $silver_medal,
            "bronze" => $bronze_medal
        ];
    }

    // private function getClubMedalEliminationIndividualAndTeam($club_id, $category){
    //     ArcheryEventElimination::
    // }
}
