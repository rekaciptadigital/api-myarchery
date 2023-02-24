<?php

namespace App\BLoC\App\Series;

use App\Models\ArcheryEvent;
use App\Models\ArcherySerie;
use App\Models\ArcherySeriesCategory;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class GetListCategorySeries extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $serie_id = $parameters->get("serie_id");
        $series = ArcherySerie::find($serie_id);
        if (!$series) {
            throw new BLoCException("series tidak tersedia");
        }


        $series_category = ArcherySeriesCategory::select("archery_serie_categories.*", "archery_event_series.event_id as event_id")
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_serie_categories.team_category_id")
            ->leftJoin("archery_event_series", "archery_event_series.id", '=', 'archery_serie_categories.serie_id')
            ->where("archery_serie_categories.serie_id", $series->id)
            ->whereIn("archery_serie_categories.team_category_id", ["individu male", "individu female"])
            ->orderBy("archery_master_team_categories.short")
            ->get();

        foreach ($series_category as $sc) {
            $event = ArcheryEvent::find($sc->event_id);
            if (!$event) {
                throw new BLoCException("event tidk tersedia");
            }
            $sc["event_detail"] = $event->getDetailEventById($event->id);
        }

        $output = [];
        $output["detail_series"] = $series;
        $output["category_series"] = $series_category;
        return $output;
    }

    protected function validation($parameters)
    {
        return [
            "serie_id" => "required|integer",
        ];
    }
}
