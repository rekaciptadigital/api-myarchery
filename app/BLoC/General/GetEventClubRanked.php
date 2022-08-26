<?php

namespace App\BLoC\General;

use DAI\Utils\Abstracts\Retrieval;
use App\Libraries\ClubRanked;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryMasterTeamCategory;
use App\Models\ArcheryScoring;
use DAI\Utils\Exceptions\BLoCException;

class GetEventClubRanked extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get("event_id");
        $rules_rating_club = $parameters->get("rules_rating_club") == null ? 1 : $parameters->get("rules_rating_club");
        $group_category_id = $parameters->get("group_category_id") == null ? 0 : $parameters->get("group_category_id");
        $age_category_id = $parameters->get("age_category_id");
        $competition_category_id = $parameters->get("competition_category_id");
        $distance_id = $parameters->get("distance_id");

        return ClubRanked::getEventRanked($event_id, $rules_rating_club, $group_category_id, $age_category_id, $competition_category_id, $distance_id);
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
                    $category_detail_individu = ArcheryEventCategoryDetail::where("event_id", $category->event_id)
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
}
