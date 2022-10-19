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

        $config_target_face = ConfigTargetFace::where("event_id", $event_id)->first();
        if ($config_target_face) {
            if ($config_target_face->implement_all == 0) {
                $config_target_face_per_category = ConfigTargetFacePerCategory::where("config_id", $config_target_face)->get();
                $config_target_face->categories_config = $config_target_face_per_category;
            }
        }

        return $config_target_face;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required|exists:archery_events,id',
        ];
    }
}
