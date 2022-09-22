<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\AdminRole;
use App\Models\ArcheryEvent;
use App\Models\ArcheryScoring;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryMasterTeamCategory;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetParticipantScoreEventSelection extends Retrieval
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
        $admin = Auth::user();
        $name = $parameters->get("name");
        $event_category_id = $parameters->get('event_category_id');
        $standings_type = $parameters->get("standings_type");

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

        $session_qualification = [];
        for ($i = 0; $i < $category_detail->session_in_qualification; $i++) {
            $session_qualification[] = $i + 1;
        }

        $session_elimination = [];
        for ($i = 0; $i < env('COUNT_STAGE_ELIMINATION_SELECTION'); $i++) {
            $session_elimination[] = $i + 1;
        }

        if ($category_detail->category_team == "Individual") {
            //filter klasemen
            if ($standings_type == 3) {
                return ArcheryScoring::getScoringRankByCategoryId($event_category_id, 3, $session_qualification);
            } else if ($standings_type == 4) {
                return app('App\BLoC\Web\ArcheryScoring\GetParticipantScoreEliminationSelectionLiveScore')->getListMemberScoringIndividual($event_category_id, 4, $session_elimination, $name, $event->id);
            } else {
                return $this->getListMemberScoringIndividual($event_category_id, $session_qualification, $session_elimination, $name, $event->id);
            }
        }
    }


    protected function validation($parameters)
    {
        return [
            "event_category_id" => "required"
        ];
    }

    public function getListMemberScoringIndividual($category_id, $session_qualification, $session_elimination, $name, $event_id)
    {
        $data_scoring = ArcheryScoring::getScoringRankByCategoryIdForEventSelection($category_id, $session_qualification, $session_elimination, true, $name);
        $category = ArcheryEventCategoryDetail::find($category_id);

        $data_collection = collect($data_scoring);
        $sorted_data = $data_collection->sortByDesc("all_total_irat")->toArray();
        $response = [];
        foreach ($sorted_data as $data) {
            array_push($response, $data);
        }
        return $response;
    }
}
