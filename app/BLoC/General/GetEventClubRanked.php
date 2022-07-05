<?php

namespace App\BLoC\General;

use DAI\Utils\Abstracts\Retrieval;
use App\Libraries\ClubRanked;
use App\Models\ArcheryEventCategoryDetail;

class GetEventClubRanked extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get("event_id");
        // $category_events = ArcheryEventCategoryDetail::where("event_id", $event_id)->where("is_join_eliminasi", 1)
        //     ->where("is_show", 1)
        //     ->get()
        //     ->groupBy(["competition_category_id", "age_category_id"]);

        // foreach ($category_events as $key1 => $value1) {
        //     foreach ($value1 as $key2 => $value2) {
        //         foreach ($value2 as $key3 => $value3) {
        //             return $value3;
        //         }
        //     }
        // }
        return ClubRanked::getEventRanked($event_id);
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required'
        ];
    }
}
