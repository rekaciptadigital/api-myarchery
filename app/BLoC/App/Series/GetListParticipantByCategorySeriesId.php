<?php

namespace App\BLoC\App\Series;

use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventSerie;
use App\Models\ArcherySerie;
use App\Models\ArcherySeriesCategory;
use App\Models\ArcherySeriesUserPint;
use App\Models\ArcherySeriesUserPoint;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class GetListParticipantByCategorySeriesId extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $category_series_id = $parameters->get("category_serie_id");
        $category_series = ArcherySeriesCategory::find($category_series_id);
        if (!$category_series) {
            throw new BLoCException("category series tidak tersedia");
        }

        $output = [];
        $serie = ArcherySerie::find($category_series->serie_id);
        $output["detail_series"] = $serie;
        $output["detail_category"] = $category_series;
        $output["user_poin"] = ArcherySeriesUserPoint::getUserSeriePointByCategory($category_series_id);
        return $output;
    }

    protected function validation($parameters)
    {
        return [
            "category_serie_id" => "required|integer",
        ];
    }
}
