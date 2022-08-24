<?php

namespace App\BLoC\General;

use App\Models\ArcheryMasterAgeCategory;
use DAI\Utils\Abstracts\Retrieval;

class GetAgeCategoryByEventId extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $rating_flag = [1, 2, 3];
        $categories = ArcheryMasterAgeCategory::select("archery_master_age_categories.*")->where("event_id", $parameters->get("event_id"))
            ->join("archery_event_category_details", "archery_event_category_details.age_category_id", "=", "archery_master_age_categories.id")
            ->distinct()
            ->get();

        $data = [];
        foreach ($rating_flag as $rf) {
            foreach ($categories as $c) {
                
            }
        }

        return $categories;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|integer|exists:archery_events,id"
        ];
    }
}
