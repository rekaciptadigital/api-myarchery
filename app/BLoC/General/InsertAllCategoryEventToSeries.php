<?php

namespace App\BLoC\General;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventSerie;
use App\Models\ArcherySerie;
use App\Models\ArcherySeriesCategory;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class InsertAllCategoryEventToSeries extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $series_id = $parameters->get("series_id");
        $event_id = $parameters->get("event_id");

        $series = ArcherySerie::find($series_id);

        $event_series = ArcheryEventSerie::where("event_id", $event_id)
            ->where("serie_id", $series_id)
            ->first();

        if (!$event_series) {
            throw new BLoCException("event series belum di set");
        }

        $list_category = ArcheryEventCategoryDetail::where("event_id", $event_id)->get();

        if ($list_category->count() > 0) {
            foreach ($list_category as $key => $category) {
                $check_is_exists = ArcherySeriesCategory::where("serie_id", $series->id)
                    ->where("age_category_id", $category->age_category_id)
                    ->where("competition_category_id", $category->competition_category_id)
                    ->where("distance_id", $category->distance_id)
                    ->where("team_category_id", $category->team_category_id)
                    ->first();
                if (!$check_is_exists) {
                    ArcherySeriesCategory::saveArcherySeriesCategory($series, $category);
                }
            }

            return "success";
        }

        throw new BLoCException("category empty");
    }

    protected function validation($parameters)
    {
        return [
            "series_id" => "required|integer|exists:archery_series,id",
            "event_id" => "required|integer|exists:archery_events,id"
        ];
    }
}
