<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryScoring;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryMasterTeamCategory;
use App\Models\ArcheryEventParticipant;
use App\Models\TeamMemberSpecial;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Redis;

class GetParticipantScoreQualification extends Retrieval
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
        $event_category_id = $parameters->get('event_category_id');
        $category_detail = ArcheryEventCategoryDetail::find($event_category_id);
        if (!$category_detail) {
            throw new BLoCException("category not found");
        }

        // $data = Redis::get($category_detail->id . "_LIVE_SCORE");
        // if ($data) {
        //     return json_decode($data);
        // }

        $team_category = ArcheryMasterTeamCategory::find($category_detail->team_category_id);
        if (!$category_detail) {
            throw new BLoCException("team category not found");
        }

        $session = [];
        for ($i = 0; $i < $category_detail->session_in_qualification; $i++) {
            $session[] = $i + 1;
        }

        if (strtolower($team_category->type) == "team") {
            if ($team_category->id == "mix_team") {
                $data = ArcheryEventParticipant::mixTeamBestOfThree($category_detail, 1);
            } else {
                $data = ArcheryEventParticipant::teamBestOfThree($category_detail, 1);
            }
        }

        if (strtolower($team_category->type) == "individual") {
            $data = ArcheryScoring::getScoringRankByCategoryId($event_category_id, $score_type, $session, false, null, false, 1);
        }

        $redis = Redis::connection();
        $redis->set($event_category_id . "_LIVE_SCORE", json_encode($data), "EX", 60 * 60 * 24 * 7);

        return $data;

        throw new BLoCException("gagal get live score");
    }

    protected function validation($parameters)
    {
        return [];
    }
}
