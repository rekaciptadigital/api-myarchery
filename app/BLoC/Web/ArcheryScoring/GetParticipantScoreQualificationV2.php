<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\AdminRole;
use App\Models\ArcheryEvent;
use App\Models\ArcheryScoring;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryMasterTeamCategory;
use App\Models\TeamMemberSpecial;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetParticipantScoreQualificationV2 extends Retrieval
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
        $score_type = $parameters->get('score_type') ?? 1;
        $admin = Auth::user();
        $name = $parameters->get("name");
        $event_category_id = $parameters->get('event_category_id');
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

        if ($event->admin_id !== $admin->id) {
            $role = AdminRole::where("admin_id", $admin->id)->where("event_id", $event->id)->first();
            if (!$role || $role->role_id != 6) {
                throw new BLoCException("you are not owner this event");
            }
        }

        $session = [];
        for ($i = 0; $i < $category_detail->session_in_qualification; $i++) {
            $session[] = $i + 1;
        }

        if ($category_detail->category_team == "Individual") {
            return $this->getListMemberScoringIndividual($event_category_id, $score_type, $session, $name, $event->id);
        }


        if (strtolower($team_category->type) == "team") {
            if ($team_category->id == "mix_team") {
                return $this->mixTeamBestOfThree($category_detail, $team_category, $session);
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

    public function getListMemberScoringIndividual($category_id, $score_type, $session, $name, $event_id)
    {
        $qualification_member = ArcheryScoring::getScoringRankByCategoryId($category_id, $score_type, $session, true, $name);
        // if ($score_type == 3) $qualification_member = ArcheryScoring::getScoringRankByCategoryId($category_id, $score_type, $session, false, $name);
        $category = ArcheryEventCategoryDetail::find($category_id);
        $total_rambahan = $category->count_stage;

        $qualification_rank = ArcheryScoring::getScoringRank($category->distance_id, $category->team_category_id, $category->competition_category_id, $category->age_category_id, null, $score_type, $event_id);

        $response = [];

        foreach ($qualification_member as $key1 => $value1) {
            foreach ($qualification_rank as $key2 => $value2) {
                if ($value1["member"]["id"] === $value2["member"]["id"]) {
                    $value1["rank"] = $key2 + 1;
                    $value1["have_shoot_off"] = $value2["have_shoot_off"];
                    array_push($response, $value1);
                    break;
                }
            }
        }

        return $response;
    }

    public function mixTeamBestOfThree($category_detail, $team_category, $session)
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
                    if ($value->is_special_team_member == 1) {
                        $tem_member_special = TeamMemberSpecial::where("participant_team_id", $value->id)->get();
                        foreach ($tem_member_special as $tms_key => $tms) {
                            if ($tms->participant_individual_id == $male_rank["member"]["participant_id"]) {
                                foreach ($male_rank["total_per_points"] as $p => $t) {
                                    $total_per_point[$p] = isset($total_per_point[$p]) ? $total_per_point[$p] + $t : $t;
                                }
                                $total = $total + $male_rank["total"];
                                $club_members[] = $male_rank["member"];
                            }
                        }
                    } else {
                        $check_is_exists = TeamMemberSpecial::where("participant_individual_id", $male_rank["member"]["participant_id"])->first();
                        if ($check_is_exists) {
                            continue;
                        }
                        foreach ($male_rank["total_per_points"] as $p => $t) {
                            $total_per_point[$p] = isset($total_per_point[$p]) ? $total_per_point[$p] + $t : $t;
                        }
                        $total = $total + $male_rank["total"];
                        $club_members[] = $male_rank["member"];
                    }
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
                    if ($value->is_special_team_member == 1) {
                        $tem_member_special = TeamMemberSpecial::where("participant_team_id", $value->id)->get();
                        foreach ($tem_member_special as $tms_key => $tms) {
                            if ($tms->participant_individual_id == $female_rank["member"]["participant_id"]) {
                                foreach ($female_rank["total_per_points"] as $p => $t) {
                                    $total_per_point[$p] = isset($total_per_point[$p]) ? $total_per_point[$p] + $t : $t;
                                }
                                $total = $total + $female_rank["total"];
                                $club_members[] = $female_rank["member"];
                            }
                        }
                    } else {
                        $check_is_exists = TeamMemberSpecial::where("participant_individual_id", $female_rank["member"]["participant_id"])->first();
                        if ($check_is_exists) {
                            continue;
                        }
                        foreach ($female_rank["total_per_points"] as $p => $t) {
                            $total_per_point[$p] = isset($total_per_point[$p]) ? $total_per_point[$p] + $t : $t;
                        }
                        $total = $total + $female_rank["total"];
                        $club_members[] = $female_rank["member"];
                    }
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
        return $new_array;
    }
}
