<?php

namespace App\BLoC\Web\ConfigTargetFace;

use App\Models\ArcheryEvent;
use App\Models\ConfigTargetFace;
use App\Models\ConfigTargetFacePerCategory;
use DAI\Utils\Abstracts\Retrieval;

class SetConfigTargetFace extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get("event_id");
        $event = ArcheryEvent::find($event_id);
        $highest_score = $parameters->get("highest_score");
        $score_x = $parameters->get("score_x");
        $implement_all = $parameters->get("implement_all");
        $categories_config = $parameters->get("categories_config");
        $active_setting = $parameters->get("active_setting");

        // reset config
        $config_target_face = ConfigTargetFace::where("event_id", $event->id)->first();
        if ($config_target_face) {
            $config_target_face_per_category = ConfigTargetFacePerCategory::where("config_id", $config_target_face->id)->get();
            foreach ($config_target_face_per_category as $key => $value) {
                $value->delete();
            }
            $config_target_face->delete();
        }

        // set ulang config
        // total_ring highest_score score_x implement_all
        if ($active_setting == 1) {
            $new_config_target_face = new ConfigTargetFace();
            $new_config_target_face->event_id = $event->id;
            $new_config_target_face->highest_score = $highest_score;
            $new_config_target_face->score_x = $score_x;
            $new_config_target_face->implement_all = $implement_all;
            $new_config_target_face->save();

            if ($implement_all == 0) {
                foreach ($categories_config as $value) {
                    $new_config_target_face_per_category = new ConfigTargetFacePerCategory();
                    $new_config_target_face->highest_score = $value["highest_score"];
                    $new_config_target_face->score_x = $value["score_x"];
                    $new_config_target_face->categories = json_encode($value["categories"]);
                    $new_config_target_face_per_category->save();
                }
            }
        }

        if ($new_config_target_face->implement_all == 0) {
            $new_config_target_face->categories_config = ConfigTargetFacePerCategory::where("config_id", $new_config_target_face->id)->get();
        }

        return $new_config_target_face;
    }

    protected function validation($parameters)
    {
        $rules = [
            'event_id' => 'required|exists:archery_events,id',
            'highest_score' => "required|numeric|max:12",
            "score_x" => "required|numeric|in:0,1,2,3,4,5,6,7,8,9,10,11,12",
            "implement_all" => "required|in:1,0",
            "active_setting" => "required|in:1,0"
        ];

        if ($parameters->get("implement_all") == 0) {
            $rules["categories_config"] = "required|array|min:1";
            $rules["categories_config.*.highest_score"] = "required|integer|max:20";
            $rules["categories_config.*.score_x"] = "required|in:1,2";
            $rules["categories_config.*.categories"] = "required|array|min:1";
            $rules["categories_config.*.categories.*.competition_category_id"] = "required|exists:archery_master_competition_categories,id";
            $rules["categories_config.*.categories.*.distance_id"] = "required|exists:archery_master_distances,id";
            $rules["categories_config.*.categories.*.age_category_id"] = "required|exists:archery_master_age_categories,id";
        }

        return $rules;
    }
}
