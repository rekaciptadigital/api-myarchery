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
        return $list_category_collection;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|integer",
            "type" => "in:Individual,Team"
        ];
    }
}
