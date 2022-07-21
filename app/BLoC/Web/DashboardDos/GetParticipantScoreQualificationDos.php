<?php

namespace App\BLoC\Web\DashboardDos;

use App\Models\ArcheryEvent;
use App\Models\ArcheryScoring;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryMasterTeamCategory;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetParticipantScoreQualificationDos extends Retrieval
{
    var $total_per_points = [
        "" => 0,
        "1" => 0,
        "2" => 0,
        "3" => 0,
        "4" => 0,
        "5" => 0,
        "6" => 0,
        "7" => 0,
        "8" => 0,
        "9" => 0,
        "10" => 0,
        "x" => 0,
        "m" => 0,
    ];

    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $score_type = 1;
        $name = $parameters->get("name");
        $event_category_id = $parameters->get('event_category_id');
        $filter_session = $parameters->get('session');

        $category_detail = ArcheryEventCategoryDetail::find($event_category_id);
        if (!$category_detail) {
            throw new BLoCException("category tidak ditemukan");
        }

        $team_category = ArcheryMasterTeamCategory::find($category_detail->team_category_id);
        if (!$team_category) {
            throw new BLoCException("team category not found");
        }

        $event = ArcheryEvent::find($category_detail->event_id);
        if (!$event) {
            throw new BLoCException("CATEGORY INVALID");
        }

        if ($filter_session) {
            $session = [];
            for ($i=0; $i < $filter_session; $i++) { 
                if ($filter_session == 2) {
                    if ($filter_session > $category_detail->session_in_qualification) throw new BLoCException("Data pada sesi ini tidak ditemukan");
                    if ($i == 0) continue;
                }

                if ($filter_session == 3) {
                    if ($filter_session > $category_detail->session_in_qualification) throw new BLoCException("Data pada sesi ini tidak ditemukan");
                    if ($i == 0) continue;
                    if ($i == 1) continue; 
                }
                $session[] = $i+1;
            }
        } else {
            $session = [];
            for ($i=0; $i < $category_detail->session_in_qualification; $i++) { 
                $session[] = $i+1;
            }
        }

        if ($category_detail->category_team == "Individual") {
            return $this->getListMemberScoringIndividual($event_category_id, $score_type, $session, $name, $event->id);
        }


        if (strtolower($team_category->type) == "team") {
            if ($team_category->id == "mix_team") {
                return $this->mixTeamBestOfThree($category_detail, $team_category, $session);
            } else {
                return $this->teamBestOfThree($category_detail, $team_category, $session);
            }
        }
    }


    protected function validation($parameters)
    {
        return [
            "event_category_id" => "required"
        ];
    }

    private function getListMemberScoringIndividual($category_id, $score_type, $session, $name, $event_id)
    {
        $qualification_member = ArcheryScoring::getScoringRankByCategoryId($category_id, $score_type, $session, false, $name);
        $category = ArcheryEventCategoryDetail::find($category_id);

        if ($session[0] == 2) {
            foreach($qualification_member as $key => $value) {
                $qualification_member[$key]["rank"] = $key + 1;
            }
            return $qualification_member;
        }

        if ($session[0] == 3) {
            foreach($qualification_member as $key => $value) {
                $qualification_member[$key]["rank"] = $key + 1;
            }
            return $qualification_member;
        }

        $qualification_rank = ArcheryScoring::getScoringRank($category->distance_id, $category->team_category_id, $category->competition_category_id, $category->age_category_id, $category->gender_category, $score_type, $event_id);


        $response = [];

        foreach ($qualification_member as $key1 => $value1) {
            foreach ($qualification_rank as $key2 => $value2) {
                if ($value1["member"]["id"] === $value2["member"]["id"]) {
                    // $value1["rank"] = $key2 + 1;
                    $value1["have_shoot_off"] = $value2["have_shoot_off"];
                    array_push($response, $value1);
                    break;
                }
            }
        }

        // number of rank
        foreach($response as $key => $value) {
            $response[$key]["rank"] = $key + 1;
        }

        return $response;
    }

    private function teamBestOfThree($category_detail, $team_category, $session)
    {
        $team_cat = ($team_category->id) == "male_team" ? "individu male" : "individu female";
        $category_detail_team = ArcheryEventCategoryDetail::where("event_id", $category_detail->event_id)
            ->where("age_category_id", $category_detail->age_category_id)
            ->where("competition_category_id", $category_detail->competition_category_id)
            ->where("distance_id", $category_detail->distance_id)
            ->where("team_category_id", $team_cat)->first();
        $qualification_rank = ArcheryScoring::getScoringRankByCategoryId($category_detail_team->id, 1, $session);

        $participant_club = [];
        $sequence_club = [];
        $participants = ArcheryEventParticipant::select("archery_event_participants.*", "archery_clubs.name as club_name")
            ->where("event_category_id", $category_detail->id)
            ->where("status", 1)
            ->leftJoin("archery_clubs", "archery_event_participants.club_id", "=", "archery_clubs.id")->get();
        foreach ($participants as $key => $value) {
            $club_members = [];
            $total_per_point = $this->total_per_points;
            $total = 0;
            $sequence_club[$value->club_id] = isset($sequence_club[$value->club_id]) ? $sequence_club[$value->club_id] + 1 : 1;
            foreach ($qualification_rank as $k => $member_rank) {
                if ($value->club_id == $member_rank["club_id"]) {
                    if ($member_rank["total"]  < 1) {
                        continue;
                    }
                    foreach ($member_rank["total_per_points"] as $p => $t) {
                        $total_per_point[$p] = isset($total_per_point[$p]) ? $total_per_point[$p] + $t : $t;
                    }
                    $total = $total + $member_rank["total"];
                    $club_members[] = $member_rank["member"];
                    unset($qualification_rank[$k]);
                }
                if (count($club_members) == 3)
                    break;
            }
            $participant_club[] = [
                "participant_id" => $value->id,
                "club_id" => $value->club_id,
                "club_name" => $value->club_name,
                "team" => $value->club_name . " " . $sequence_club[$value->club_id],
                "total" => $total,
                "total_x_plus_ten" => isset($total_per_point["x"]) ? $total_per_point["x"] + $total_per_point["10"] : 0,
                "total_x" => isset($total_per_point["x"]) ? $total_per_point["x"] : 0,
                "total_per_points" => $total_per_point,
                "total_tmp" => ArcheryScoring::getTotalTmp($total_per_point, $total),
                "teams" => $club_members
            ];
        }
        usort($participant_club, function ($a, $b) {
            return $b["total_tmp"] > $a["total_tmp"] ? 1 : -1;
        });

        $new_array = [];
        foreach ($participant_club as $key => $value) {
            if (count($value["teams"]) == 3) {
                array_push($new_array, $value);
            }
        }

         // number of rank
         foreach($new_array as $key => $value) {
            $new_array[$key]["rank"] = $key + 1;
        }
        
        return $new_array;
    }

    private function mixTeamBestOfThree($category_detail, $team_category, $session)
    {
        $category_detail_male = ArcheryEventCategoryDetail::where("event_id", $category_detail->event_id)
            ->where("age_category_id", $category_detail->age_category_id)
            ->where("competition_category_id", $category_detail->competition_category_id)
            ->where("distance_id", $category_detail->distance_id)
            ->where("team_category_id", "individu male")->first();
        $qualification_male = ArcheryScoring::getScoringRankByCategoryId($category_detail_male->id, 1, $session);

        $category_detail_female = ArcheryEventCategoryDetail::where("event_id", $category_detail->event_id)
            ->where("age_category_id", $category_detail->age_category_id)
            ->where("competition_category_id", $category_detail->competition_category_id)
            ->where("distance_id", $category_detail->distance_id)
            ->where("team_category_id", "individu female")->first();
        $qualification_female = ArcheryScoring::getScoringRankByCategoryId($category_detail_female->id, 1, $session);

        $participant_club = [];
        $sequence_club = [];
        $participants = ArcheryEventParticipant::select("archery_event_participants.*", "archery_clubs.name as club_name")->where("event_category_id", $category_detail->id)
            ->where("status", 1)
            ->leftJoin("archery_clubs", "archery_event_participants.club_id", "=", "archery_clubs.id")->get();
        foreach ($participants as $key => $value) {
            $club_members = [];
            $total_per_point = $this->total_per_points;
            $total = 0;
            $sequence_club[$value->club_id] = isset($sequence_club[$value->club_id]) ? $sequence_club[$value->club_id] + 1 : 1;
            foreach ($qualification_male as $k => $male_rank) {
                if ($value->club_id == $male_rank["club_id"]) {
                    if ($male_rank["total"]  < 1) {
                        continue;
                    }
                    foreach ($male_rank["total_per_points"] as $p => $t) {
                        $total_per_point[$p] = isset($total_per_point[$p]) ? $total_per_point[$p] + $t : $t;
                    }
                    $total = $total + $male_rank["total"];
                    $club_members[] = $male_rank["member"];
                    unset($qualification_male[$k]);
                }
                if (count($club_members) == 1)
                    break;
            }
            foreach ($qualification_female as $ky => $female_rank) {
                if ($value->club_id == $female_rank["club_id"]) {
                    if ($female_rank["total"]  < 1) {
                        continue;
                    }
                    foreach ($female_rank["total_per_points"] as $p => $t) {
                        $total_per_point[$p] = isset($total_per_point[$p]) ? $total_per_point[$p] + $t : $t;
                    }
                    $total = $total + $female_rank["total"];
                    $club_members[] = $female_rank["member"];
                    unset($qualification_female[$ky]);
                }
                if (count($club_members) == 2)
                    break;
            }

            $participant_club[] = [
                "participant_id" => $value->id,
                "club_id" => $value->club_id,
                "club_name" => $value->club_name,
                "team" => $value->club_name . " " . $sequence_club[$value->club_id],
                "total" => $total,
                "total_x_plus_ten" => isset($total_per_point["x"]) ? $total_per_point["x"] + $total_per_point["10"] : 0,
                "total_x" => isset($total_per_point["x"]) ? $total_per_point["x"] : 0,
                "total_per_points" => $total_per_point,
                "total_tmp" => ArcheryScoring::getTotalTmp($total_per_point, $total),
                "teams" => $club_members
            ];
        }
        usort($participant_club, function ($a, $b) {
            return $b["total_tmp"] > $a["total_tmp"] ? 1 : -1;
        });

        $new_array = [];
        foreach ($participant_club as $key => $value) {
            if (count($value["teams"]) == 2) {
                array_push($new_array, $value);
            }
        }
        
        // number of rank
        foreach($new_array as $key => $value) {
            $new_array[$key]["rank"] = $key + 1;
        }

        return $new_array;
    }
}
