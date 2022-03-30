<?php

namespace App\BLoC\General\CategoryDetail;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\QandA;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class GetListCategoryByEventId extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get("event_id");
        $event = ArcheryEvent::find($event_id);
        $type = $parameters->get("type");

        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        $list_category_query = ArcheryEventCategoryDetail::select("archery_event_category_details.*")->where("event_id", $event_id);

        $list_category_query->when($type, function ($query) use ($type) {
            return $query->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
                ->where("archery_master_team_categories.type", $type);
        });

        $list_category_collection = $list_category_query->get();

        $output = [];
        $response = [];

        if ($list_category_collection->count() > 0) {
            foreach ($list_category_collection as $category) {
                $response["id"] = $category->id;
                $response["event_id"] = $category->event_id;
                $response["age_category_id"] = $category->age_category_id;
                $response["competition_category_id"] = $category->competition_category_id;
                $response["distance_id"] = $category->distance_id;
                $response["team_category_id"] = $category->team_category_id;
                $response["is_show"] = $category->is_show;
                $response["category_team"] = $category->category_team;
                $response["gender_category"] = $category->gender_category;
                $response["label_category"] = $category->label_category;

                array_push($output, $response);
            }
        }
        return $output;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|integer",
            "type" => "in:Individual,Team"
        ];
    }
}
