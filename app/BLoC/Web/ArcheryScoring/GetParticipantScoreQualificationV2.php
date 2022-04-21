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
        $club_name = $parameters->get("club_name");
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

        $qualification_rank = ArcheryScoring::getScoringRankByCategoryId($event_category_id, $score_type, $session, true, $name);
        return $qualification_rank;

        return [];
    }


    protected function validation($parameters)
    {
        return [];
    }
}
