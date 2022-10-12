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
use Illuminate\Support\Facades\Storage;
use PDFv2;
use Illuminate\Support\Facades\Redis;
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

        $pages = array();
        $logo_archery = '<img src="' . Storage::disk('public')->path("logo/logo-archery.png") . '" alt="" width="80%"></img>';

        $archery_event = ArcheryEvent::find($event_id);
        if (!$archery_event) throw new BLoCException("event tidak terdaftar");

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
        $logo_archery_cover = '<img src="' . Storage::disk('public')->path("logo/logo-archery.png") . '" alt="" width="60%"></img>';
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
        $data_medal_standing = $this->getMedalStanding($event_id);

        if (count($data_medal_standing["datatable"]) > 0) {
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



            // =============================== data ======================================
            foreach ($data_medal_standing['datatable'] as $key => $dms) {
                $pages[] = view('report_medal_club/dataTable', [
                    'logo_event' => $logo_event,
                    "dms" => $dms,
                    'logo_archery' => $logo_archery,
                    'event_name_report' => $event_name_report,
                    'event_date_report' => $event_date_report,
                    'event_location_report' => $event_location_report,
                    'headers' => $data_medal_standing['title_header']['category'],
                    "rank" => $key + 1,
                    "category" => $data_medal_standing["title_header"]["category"],
                    "club_name" => $dms["club_name"],
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

    protected function getMedalStanding($event_id)
    {
        $data = ClubRanked::getEventRanked($event_id, 1, null);
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
            $count_rowspan = [
                "count_rowspan" => count($age_category)
            ];
            array_push($title_header['category'][$competition->competition_category], $count_colspan, $count_rowspan);
        }

        $result = [];
        $detail_club_with_medal_response = [];
        if (count($data) > 0) {
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
        }

        // start: total medal emas, perak, perunggu dari setiap kategori semua klub
        $array_of_total_medal_by_category = [];
        if (count($result) > 0) {
            $total_array_category = count($result[0]['medal_array']);
            for ($i = 0; $i < $total_array_category; $i++) {
                $total_medal_by_category = 0;
                for ($j = 0; $j < count($result); $j++) {
                    $total_medal_by_category += $result[$j]['medal_array'][$i];
                }
                array_push($array_of_total_medal_by_category, $total_medal_by_category);
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
            // end: total medal emas, perak, perunggu secara keseluruhan dari semua klub 
        }
        // end: total medal emas, perak, perunggu dari setiap kategori semua klub



        $response = [
            'title_header' => $title_header,
            'datatable' => $result,
            'total_medal_by_category' => $array_of_total_medal_by_category,
            'total_medal_by_category_all_club' => isset($array_of_total_medal_by_category_all_club) ? $array_of_total_medal_by_category_all_club : []
        ];

        return $response;
    }

    protected function getData($category_detail_id, $type, $event_id)
    {
        $data_report = [];
        $category_id = null;
        $elimination_rank = 0;

        $members = ArcheryEventEliminationMember::select("*", "archery_event_category_details.id as category_details_id", "archery_event_participant_members.id as participant_member_id", DB::RAW('date(archery_event_elimination_members.created_at) as date'))
            ->join('archery_event_participant_members', 'archery_event_participant_members.id', '=', 'archery_event_elimination_members.member_id')
            ->join('archery_event_participants', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
            ->join('archery_event_category_details', 'archery_event_category_details.id', '=', 'archery_event_participants.event_category_id')
            ->where("archery_event_category_details.id", $category_detail_id)
            ->where("archery_event_participants.event_id", $event_id)
            ->where(function ($query) use ($type) {
                if ($type == "elimination") {
                    $query->where("archery_event_elimination_members.elimination_ranked", '>', 0);
                    $query->where("archery_event_elimination_members.elimination_ranked", '<=', 3);
                    $query->orderBy('archery_event_elimination_members.elimination_ranked', 'ASC');
                } else if ($type == "qualification") {
                    $query->where("archery_event_elimination_members.position_qualification", '>', 0);
                    $query->where("archery_event_elimination_members.position_qualification", '<=', 3);
                    $query->orderBy('archery_event_elimination_members.position_qualification', 'ASC');
                } else {
                    $query->orderBy('archery_event_elimination_members.position_qualification', 'ASC');
                }
            })
            ->orderBy('archery_event_participants.event_category_id', 'ASC')
            ->orderBy('archery_event_category_details.team_category_id', 'DESC')
            ->get();


        if ($members) {
            foreach ($members as $member) {

                $categoryLabel = ArcheryEventCategoryDetail::getCategoryLabelComplete($member->category_details_id);

                // if ($member->elimination_ranked == 1 || $member->position_qualification == 1) {
                //     $medal = 'Gold';
                // } else if ($member->elimination_ranked == 2 || $member->position_qualification == 2) {
                //     $medal = 'Silver';
                // } else {
                //     $medal = 'Bronze';
                // }

                if ($type == "elimination") {
                    $elimination_rank = $member->elimination_ranked;
                    if ($member->elimination_ranked == 1) {
                        $medal = 'Gold';
                    } else if ($member->elimination_ranked == 2) {
                        $medal = 'Silver';
                    } else {
                        $medal = 'Bronze';
                    }
                } elseif ($type == "qualification") {
                    if ($member->position_qualification == 1) {
                        $medal = 'Gold';
                    } else if ($member->position_qualification == 2) {
                        $medal = 'Silver';
                    } else {
                        $medal = 'Bronze';
                    }
                } else {
                    $medal = '-';
                }

                $athlete = $member->name;
                $date = $member->date;

                $club = ArcheryClub::find($member->club_id);
                if (!$club) {
                    $club = '';
                } else {
                    $club = $club->name;
                }

                $category = ArcheryEventCategoryDetail::find($member->category_details_id);
                $session = [];
                for ($i = 0; $i < $category->session_in_qualification; $i++) {
                    $session[] = $i + 1;
                }
                $scoring = ArcheryScoring::generateScoreBySession($member->participant_member_id, 1, $session);

                $data_report[] = array("athlete" => $athlete, "club" => $club, "category" => $categoryLabel, "medal" => $medal, "date" => $date, "scoring" => $scoring, "elimination_rank" => $elimination_rank);

                $category_id = $member->category_details_id;
            }
        }

        if ($type == "elimination") {
            $sorted_data = collect($data_report)->sortBy('elimination_rank')->values()->all();
            return array($sorted_data, $category_id);
        }

        $sorted_data = collect($data_report)->sortByDesc('scoring.total')->values()->all();

        return array($sorted_data, $category_id);
    }

    protected function getElimination($category_detail)
    {

        $team_category = ArcheryMasterTeamCategory::find($category_detail->team_category_id);
        if (!$team_category) throw new BLoCException("team category not found");

        if (strtolower($team_category->type) == "team") {
            $data = app('App\BLoC\Web\EventElimination\GetEventEliminationTemplate')->getTemplateTeam($category_detail);
        }

        if (strtolower($team_category->type) == "individual") {
            $data = app('App\BLoC\Web\EventElimination\GetEventEliminationTemplate')->getTemplateIndividu($category_detail);
        }

        return $data;
    }

    protected function getQualification($category_detail)
    {
        $score_type = 1;
        $name = null;
        $team_category = ArcheryMasterTeamCategory::find($category_detail->team_category_id);
        if (!$team_category) throw new BLoCException("team category not found");

        $event = ArcheryEvent::find($category_detail->event_id);
        if (!$event) throw new BLoCException("CATEGORY INVALID");

        $session = [];
        for ($i = 0; $i < $category_detail->session_in_qualification; $i++) {
            $session[] = $i + 1;
        }

        if ($category_detail->category_team == "Individual") {
            // $data = app('App\BLoC\Web\ArcheryScoring\GetParticipantScoreQualificationV2')->getListMemberScoringIndividual($category_detail->id, $score_type, $session, $name, $event->id);
            $qualification_member = ArcheryScoring::getScoringRankByCategoryId($category_detail->id, $score_type, $session, false, $name);

            return $qualification_member;
        }

        if (strtolower($team_category->type) == "team") {
            if ($team_category->id == "mix_team") {
                $data = app('App\BLoC\Web\ArcheryScoring\GetParticipantScoreQualificationV2')->mixTeamBestOfThree($category_detail, $team_category, $session);
            } else {
                $data = app('App\BLoC\Web\ArcheryScoring\GetParticipantScoreQualificationV2')->teamBestOfThree($category_detail, $team_category, $session);
            }
        }

        return $data;
    }

    protected function getDataEliminationTeam($category_detail_id)
    {
        $elimination_group = ArcheryEventEliminationGroup::where('category_id', $category_detail_id)->first();
        if ($elimination_group) {
            $elimination_group_match = ArcheryEventEliminationGroupMatch::select(DB::RAW('distinct group_team_id as teamid'))->where('elimination_group_id', $elimination_group->id)->get();

            $data = array();
            foreach ($elimination_group_match as $key => $value) {

                $elimination_group_team = ArcheryEventEliminationGroupTeams::where('id', $value->teamid)->first();

                if ($elimination_group_team) {
                    if ($elimination_group_team->elimination_ranked <= 3) {
                        $data[] = [
                            'id' => $elimination_group_team->id,
                            'team_name' => $elimination_group_team->team_name,
                            'elimination_ranked' => $elimination_group_team->elimination_ranked ?? 0,
                            'category' => ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail_id),
                            'date' => $elimination_group->created_at->format('Y-m-d')
                        ];
                    } else {
                        continue;
                    }
                }
            }

            $sorted_data = collect($data)->sortBy('elimination_ranked')->values()->take(3);
            return $sorted_data;
        }
    }
}
