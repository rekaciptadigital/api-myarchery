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

        $archery_user_point = ArcherySeriesUserPoint::where("event_category_id", $category_series->id)->get();

        $output = [];
        $serie = ArcherySerie::find($category_series->serie_id);
        $output["detail_series"] = $serie;
        $output["detail_category"] = $category_series;
        if ($archery_user_point->count() > 0) {
            foreach ($archery_user_point as $aup) {
                $detail_user = User::getDetailUser($aup->user_id);
                $aup["detail_users"] = $detail_user;
                $event_series = ArcheryEventSerie::find($aup->event_serie_id);
                if (!$event_series) {
                    throw new BLoCException("event series tidak ada");
                }
                $participant = ArcheryEventParticipant::where("team_category_id", $category_series->team_category_id)
                    ->where("age_category_id", $category_series->age_category_id)
                    ->where("competition_category_id", $category_series->competition_category_id)
                    ->where("distance_id", $category_series->distance_id)
                    ->where("event_id", $event_series->event_id)
                    ->where("user_id", $aup->user_id)
                    ->where("status", 1)
                    ->first();
                if (!$participant) {
                    throw new BLoCException("participant tidak ada");
                }

                $club = ArcheryClub::find($participant->club_id);
                $aup["club_detail"] = $club;
            }
        }
        $output["user_poin"][] = $archery_user_point;
        return $output;
    }

    protected function validation($parameters)
    {
        return [
            "category_serie_id" => "required|integer",
        ];
    }
}
