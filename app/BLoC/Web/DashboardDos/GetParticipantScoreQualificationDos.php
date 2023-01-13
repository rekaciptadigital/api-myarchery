<?php

namespace App\BLoC\Web\DashboardDos;

use App\Models\ArcheryEvent;
use App\Models\ArcheryScoring;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryMasterTeamCategory;
use App\Models\TeamMemberSpecial;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

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
        "11" => 0,
        "12" => 0,
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
                return ArcheryEventParticipant::mixTeamBestOfThree($category_detail);
            } else {
                return ArcheryEventParticipant::teamBestOfThree($category_detail);
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
}
