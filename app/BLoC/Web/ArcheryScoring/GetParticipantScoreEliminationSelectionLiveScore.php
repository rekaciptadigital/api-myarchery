<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryEvent;
use App\Models\ArcheryScoring;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryMasterTeamCategory;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetParticipantScoreEliminationSelectionLiveScore extends Retrieval
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
        $score_type = 4;
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

        $session = [];
        for ($i = 0; $i < env('COUNT_STAGE_ELIMINATION_SELECTION'); $i++) {
            $session[] = $i + 1;
        }

        if ($category_detail->category_team == "Individual") {
            return $this->getListMemberScoringIndividual($event_category_id, $score_type, $session, $name, $event->id);
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
        $qualification_member = ArcheryScoring::getScoringRankByCategoryIdForEliminationSelection($category_id, $score_type, $session, false, $name);
        $category = ArcheryEventCategoryDetail::find($category_id);

        $qualification_rank = ArcheryScoring::getScoringRankForEliminationSelection($category->distance_id, $category->team_category_id, $category->competition_category_id, $category->age_category_id, null, $score_type, $event_id);

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

        // sorting by total irat
        // $data_collection = collect($response);
        // $sorted_data = $data_collection->sortByDesc("total_irat")->toArray();
        // $output = [];
        // foreach ($sorted_data as $data) {
        //     array_push($output, $data);
        // }
        // return $output;
    }

}
