<?php

namespace App\BLoC\App\Series;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryMasterAgeCategory;
use App\Models\ArcheryMasterCompetitionCategory;
use App\Models\ArcheryMasterDistanceCategory;
use App\Models\ArcheryMasterTeamCategory;
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
        $series = ArcherySerie::find($serie_id);
        if (!$series) {
            throw new BLoCException("series tidak tersedia");
        }


        $series_category = ArcherySeriesCategory::select("archery_serie_categories.*", "archery_event_series.event_id as event_id")
            ->leftJoin("archery_event_series", "archery_event_series.id", '=', 'archery_serie_categories.serie_id')
            ->where("archery_serie_categories.serie_id", $series->id)
            ->get();

        foreach ($series_category as $sc) {
            $age_category_detail = ArcheryMasterAgeCategory::find($sc->age_category_id);
            $competition_category_detail = ArcheryMasterCompetitionCategory::find($sc->competition_category_id);
            $distance_category_detail = ArcheryMasterDistanceCategory::find($sc->distance_id);
            $team_category_detail = ArcheryMasterTeamCategory::find($sc->team_category_id);
            $event = ArcheryEvent::find($sc->event_id);
            if (!$event) {
                throw new BLoCException("event tidk tersedia");
            }
            $sc["age_detail"] = $age_category_detail;
            $sc["competition_detail"] = $competition_category_detail;
            $sc["distance_detail"] = $distance_category_detail;
            $sc["team_detail"] = $team_category_detail;
            $category_label = "";
            if ($age_category_detail != null && $competition_category_detail != null && $distance_category_detail != null && $team_category_detail != null) {
                $category_label = $age_category_detail->label . "-" . $competition_category_detail->label . "-" . $distance_category_detail->label . "-" . $team_category_detail->label;
            }
            $sc["category_label"] = $category_label;
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
            // "event_id" => "required|integer"
        ];
    }
}
