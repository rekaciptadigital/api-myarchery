<?php

namespace App\BLoC\App\Series;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcherySerie;
use App\Models\ArcherySeriesCategory;
use App\Models\ArcherySeriesUserPint;
use App\Models\ArcherySeriesUserPoint;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

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
                if (!$detail_user) {
                    throw new BLoCException("user tidak ditemukan");
                }
                $aup["detail_users"] = $detail_user;
            }
        }
        $output["user_poin"][] = $aup;
        return $output;
    }

    protected function validation($parameters)
    {
        return [
            "category_serie_id" => "required|integer",
        ];
    }
}
