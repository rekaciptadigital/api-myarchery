<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryScoring;
use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class GetParticipantScoreEventSelectionLiveScore extends Retrieval
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
        $name = $parameters->get("name");
        $event_category_id = $parameters->get('event_category_id');
        $category_detail = ArcheryEventCategoryDetail::select(
            "archery_event_category_details.*",
            "archery_master_team_categories.type",
        )
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
            ->where("archery_event_category_details.id", $event_category_id)
            ->first();

        if (!$category_detail) {
            throw new BLoCException("category not found");
        }

        $session_qualification = [];
        for ($i = 0; $i < $category_detail->session_in_qualification; $i++) {
            $session_qualification[] = $i + 1;
        }

        $session_elimination = [];
        for ($i = 0; $i < $category_detail->session_in_elimination_selection; $i++) {
            $session_elimination[] = $i + 1;
        }

        if ($category_detail->type == "Individual") {
            return ArcheryScoring::getScoringRankByCategoryIdForEventSelection($event_category_id, $session_qualification, $session_elimination, $name);
        }
    }


    protected function validation($parameters)
    {
        return [
            "event_category_id" => "required"
        ];
    }
}
