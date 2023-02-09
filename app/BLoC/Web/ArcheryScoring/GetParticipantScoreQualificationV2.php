<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\AdminRole;
use App\Models\ArcheryEvent;
use App\Models\ArcheryScoring;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryMasterTeamCategory;
use App\Models\MemberRank;
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

    public function getListMemberScoringIndividual($category_id, $score_type, $session, $name, $event_id)
    {
        $qualification_member = ArcheryScoring::getScoringRankByCategoryId($category_id, $score_type, $session, true, $name);
        // if ($score_type == 3) $qualification_member = ArcheryScoring::getScoringRankByCategoryId($category_id, $score_type, $session, false, $name);
        $category = ArcheryEventCategoryDetail::find($category_id);
        $total_rambahan = $category->count_stage;

        $qualification_rank = ArcheryScoring::getScoringRank($category->distance_id, $category->team_category_id, $category->competition_category_id, $category->age_category_id, null, $score_type, $event_id);

        $response = [];

        foreach ($qualification_member as $key1 => $value1) {
            $member_rank = MemberRank::where("member_id", $value1["member"]["id"])
                ->where("category_id", $category_id)
                ->first();
            foreach ($qualification_rank as $key2 => $value2) {
                if ($value1["member"]["id"] === $value2["member"]["id"]) {
                    $rank = $key2 + 1;
                    if ($member_rank) {
                        $rank =  $member_rank->rank;
                    }
                    $value1["rank"] = $rank;
                    $value1["have_shoot_off"] = $value2["have_shoot_off"];
                    $value1["have_coint_tost"] = $value2["have_coint_tost"];
                    $value1["rank_can_change"] = json_decode($value2["rank_can_change"]);
                    array_push($response, $value1);
                    break;
                }
            }
        }

        return $response;
    }
}
