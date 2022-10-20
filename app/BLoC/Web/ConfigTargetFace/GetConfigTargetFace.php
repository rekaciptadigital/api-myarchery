<?php

namespace App\BLoC\Web\ConfigTargetFace;

use App\Models\ConfigTargetFace;
use App\Models\ConfigTargetFacePerCategory;
use DAI\Utils\Abstracts\Retrieval;

class GetConfigTargetFace extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get("event_id");

        $response = [];

        $response["event_id"] = $event_id;
        $highest_score = 10;
        $score_x = 1;
        $implement_all = 1;
        $categories_config = null;

        $config_target_face = ConfigTargetFace::where("event_id", $event_id)->first();
        if ($config_target_face) {
            $highest_score = $config_target_face->highest_score;
            $score_x = $config_target_face->score_x;
            $implement_all = $config_target_face->implement_all;
            if ($config_target_face->implement_all == 0) {
                $config_target_face_per_category = ConfigTargetFacePerCategory::where("config_id", $config_target_face)->get();
                $categories_config = $config_target_face_per_category;
            }
        }

        $response["highest_score"] = $highest_score;
        $response["score_x"] = $score_x;
        $response["implement_all"] = $implement_all;
        $response["categories_config"] = $categories_config;

        return $response;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required|exists:archery_events,id',
        ];
    }
}
