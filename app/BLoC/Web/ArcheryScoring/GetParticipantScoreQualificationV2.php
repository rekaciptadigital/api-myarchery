<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryEvent;
use App\Models\ArcheryScoring;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryMasterTeamCategory;
use App\Models\ArcheryEventParticipant;
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
        $admin = Auth::user();
        $name = $parameters->get("name");
        $event_category_id = $parameters->get('event_category_id');
        $category_detail = ArcheryEventCategoryDetail::find($event_category_id);
        if (!$category_detail) {
            throw new BLoCException("category tidak ditemukan");
        }

        $event = ArcheryEvent::find($category_detail->event_id);
        if (!$event) {
            throw new BLoCException("CATEGORY INVALID");
        }

        if ($event->admin_id !== $admin->id) {
            throw new BLoCException("you are not owner this event");
        }

        $session = [];
        for ($i = 0; $i < $category_detail->session_in_qualification; $i++) {
            $session[] = $i + 1;
        }

        if ($category_detail->category_team !== "Individual") {
            throw new BLoCException("category harus individual");
        }

        $qualification_member = ArcheryScoring::getScoringRankByCategoryId($event_category_id, $score_type, $session, true, $name);
        $qualification_rank = ArcheryScoring::getScoringRank($category_detail->distance_id, $category_detail->team_category_id, $category_detail->competition_category_id, $category_detail->age_category_id, $category_detail->gender_category, $score_type, $event->id, $parameters->get("elimination_template"));

        $response = [];

        foreach ($qualification_member as $key1 => $value1) {
            foreach ($qualification_rank as $key2 => $value2) {
                if ($value1["member"]["id"] === $value2["member"]["id"]) {
                    $value1["rank"] = $key2 + 1;
                    if (isset($value2["have_shoot_off"]) && $value2["have_shoot_off"] === 1) {
                        $value1["have_shoot_off"] = 1;
                    }
                    array_push($response, $value1);
                    break;
                }
            }
        }

        return $response;
    }


    protected function validation($parameters)
    {
        return [
            
        ];
    }
}
