<?php

namespace App\BLoC\App\Series;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventSerie;
use App\Models\ArcherySerie;
use App\Models\ArcherySeriesCategory;
use App\Models\ArcherySeriesUserPint;
use App\Models\ArcherySeriesUserPoint;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetListEventBySeriesId extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $serie_id = $parameters->get("serie_id");
        $event_series = ArcheryEventSerie::where("serie_id", $serie_id)->get();

        if ($event_series->count() > 0) {
            foreach ($event_series as $es) {
                $event = ArcheryEvent::find($es->event_id);
                if (!$event) {
                    throw new BLoCException("event tidak ditemukan");
                }

                $detail_event = $event->getDetailEventById($event->id);
                $es["detail_event"] = $detail_event;
            }
        }

        return $event_series;
    }

    protected function validation($parameters)
    {
        return [
            "serie_id" => "required|integer"
        ];
    }
}
