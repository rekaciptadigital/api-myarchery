<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\AdminRole;
use App\Models\ArcheryScoring;
use App\Models\ArcheryEventCategoryDetail;
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
        $admin = Auth::user();
        $name = $parameters->get("name");
        $event_category_id = $parameters->get('event_category_id');
        $standings_type = $parameters->get("standings_type");

        $category_detail = ArcheryEventCategoryDetail::select(
            "archery_event_category_details.*",
            "archery_master_team_categories.type",
            "archery_events.admin_id"
        )
            ->join("archery_events", "archery_events.id", "=", "archery_event_category_details.event_id")
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
            ->where("archery_event_category_details.id", $event_category_id)
            ->first();

        if (!$category_detail) {
            throw new BLoCException("category not found");
        }

        if ($category_detail->admin_id !== $admin->id) {
            $role = AdminRole::where("admin_id", $admin->id)
                ->where("event_id", $category_detail->event_id)
                ->first();
            if (!$role || $role->role_id != 6) {
                throw new BLoCException("you are not owner this event");
            }
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
            //filter klasemen
            if ($standings_type == 3) {
                return ArcheryScoring::getScoringRankByCategoryId($event_category_id, 3, $session_qualification, false, null, false);
            } else if ($standings_type == 4) {
                return ArcheryScoring::getScoringRankByCategoryIdForEliminationSelection($event_category_id, 4, $session_elimination, false, $name, false);
            } else {
                return ArcheryScoring::getScoringRankByCategoryIdForEventSelection($event_category_id, $session_qualification, $session_elimination, $name);
            }
        }
    }


    protected function validation($parameters)
    {
        return [
            "event_category_id" => "required"
        ];
    }
}
