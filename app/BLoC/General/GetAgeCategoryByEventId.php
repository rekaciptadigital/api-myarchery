<?php

namespace App\BLoC\General;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryMasterAgeCategory;
use App\Models\GroupCategory;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class GetAgeCategoryByEventId extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $rules_rating_club = [1, 2, 3];
        $categories = ArcheryEventCategoryDetail::select("archery_event_category_details.*")->where("event_id", $parameters->get("event_id"))
            ->join("archery_master_age_categories", "archery_master_age_categories.id", "=", "archery_event_category_details.age_category_id")
            ->distinct()
            ->get();

        $response_category = [];

        $data = [];
        foreach ($rules_rating_club as $rf) {
            foreach ($categories as $c) {
                if ($c->rules_rating_club == $rf) {
                    $group_category_name = $rf == 1 ? "semua kategori" : "satuan";
                    if ($c->rules_rating_club == 3) {
                        $group_category = GroupCategory::find($c->group_category_id);
                        if (!$group_category) {
                            throw new BLoCException("group category not found");
                        }
                        $group_category_name = $group_category->group_category_name;
                    }
                    $response_category["id"] = $c->id;
                    $response_category["event_id"] = $c->event_id;
                    $response_category["age_category_id"] = $c->age_category_id;
                    $response_category["distance_id"] = $c->distance_id;
                    $response_category["competition_category_id"] = $c->competition_category_id;
                    $response_category["team_category_id"] = $c->team_category_id;
                    $response_category["rating_flag"] = $c->rating_flag;
                    $response_category["label"] = $c->label_category;
                    $response_category["rules_rating_club"] = $c->rules_rating_club;
                    $response_category["rating_flag"] = $c->rating_flag;
                    $response_category["group_category_id"] = $c->group_category_id;
                    $data[$rf][$c->group_category_id][] = $response_category;
                    $data[$rf]["label"] = $group_category_name;
                }
            }
        }

        return $data;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|integer|exists:archery_events,id"
        ];
    }
}
