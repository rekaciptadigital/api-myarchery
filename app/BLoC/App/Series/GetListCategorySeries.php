<?php

namespace App\BLoC\App\Series;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcherySerie;
use App\Models\ArcherySeriesCategory;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetListCategorySeries extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $serie_id = $parameters->get("serie_id");
        // $event_id = $parameters->get("event_id");
        $series = ArcherySerie::find($serie_id);
        if (!$series) {
            throw new BLoCException("series tidak tersedia");
        }

        // $event = ArcheryEvent::find($event_id);
        // if (!$event) {
        //     throw new BLoCException("event tidak ditemukan");
        // }


        $series_category = ArcherySeriesCategory::select("archery_serie_categories.*", "archery_event_series.event_id as event_id")
            ->leftJoin("archery_event_series", "archery_event_series.id", '=', 'archery_serie_categories.serie_id')
            ->where("archery_serie_categories.serie_id", $series->id)
            ->get();

        $output = [];
        $output["detail_series"] = $series;
        // $output['detail_event'] = $event->getDetailEventById($event->id);
        $output["category_series"] = $series_category;
        return $output;
    }

    protected function validation($parameters)
    {
        return [
            "serie_id" => "required|integer",
            // "event_id" => "required|integer"
        ];
    }
}
