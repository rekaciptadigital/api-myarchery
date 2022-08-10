<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ArcheryEventCategoryDetail;
use Illuminate\Support\Facades\DB;

class ArcheryEventQualificationTime extends Model
{
    protected $table = 'archery_event_qualification_time';

    protected function getQualificationById($event_id, $category_detail_id = "", $type = null)
    {
        $qualification_query = ArcheryEventCategoryDetail::select('event_id', 'category_detail_id', 'event_start_datetime', 'event_end_datetime', 'archery_event_qualification_time.id')
            ->join("archery_event_qualification_time", "archery_event_category_details.id", "archery_event_qualification_time.category_detail_id")
            ->where("archery_event_category_details.event_id", $event_id);

        if ($type !== null) {
            $qualification_query->when($type, function ($query) use ($type) {
                return $query->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
                    ->where("archery_master_team_categories.type", $type);
            });
        }


        if (!empty($category_detail_id)) {
            $qualification_query->when($category_detail_id, function ($query) use ($category_detail_id) {
                return $query->where("archery_event_qualification_time.category_detail_id", $category_detail_id);
            });
        }


        $qualification_collection = $qualification_query->get();

        return $qualification_collection;
    }

    public static function getCategoryByDate($date, $event_id)
    {
        $category = ArcheryEventCategoryDetail::select("archery_event_category_details.*")
            ->join("archery_event_qualification_time", "archery_event_qualification_time.category_detail_id", "=", "archery_event_category_details.id")
            ->where("archery_event_category_details.event_id", $event_id)
            ->whereDate("archery_event_qualification_time.event_start_datetime", "<=", $date)
            ->get();

        return $category;
    }
}
