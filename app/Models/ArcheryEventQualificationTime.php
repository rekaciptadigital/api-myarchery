<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ArcheryEventCategoryDetail;
use DateInterval;
use DatePeriod;
use DateTime;
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

    public static function getCategoryByDate($event_id)
    {
        $event = ArcheryEvent::find($event_id);
        // Start date
        $start = strtotime($event->event_start_datetime);
        // End date
        $end = strtotime($event->event_end_datetime);

        $response = [];
        $data = [];

        $sort_day = 1;
        for ($i = $start; $i <= $end; $i += 86400) {
            $day = date("Y-m-d", $i);

            $category = ArcheryEventCategoryDetail::select("archery_event_category_details.*", "archery_event_qualification_time.event_start_datetime")
                ->leftJoin("archery_event_qualification_time", "archery_event_qualification_time.category_detail_id", "=", "archery_event_category_details.id")
                ->where("archery_event_category_details.event_id", $event_id)
                // ->whereDate("archery_event_qualification_time.event_start_datetime", $day)
                ->get();

            $cat_fix = [];
            foreach ($category as $cat) {
                if (date("Y-m-d", strtotime($cat->event_start_datetime)) == $day) {
                    $cat_fix[] = $cat;
                } elseif ($cat->event_start_datetime == null) {
                    $cat_individu = ArcheryEventCategoryDetail::select("archery_event_category_details.*", "archery_event_qualification_time.event_start_datetime")
                        ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
                        ->join("archery_event_qualification_time", "archery_event_qualification_time.category_detail_id", "=", "archery_event_category_details.id")
                        ->where("event_id", $cat->event_id)
                        ->where("age_category_id", $cat->age_category_id)
                        ->where("distance_id", $cat->distance_id)
                        ->where("competition_category_id", $cat->competition_category_id)
                        ->where("archery_master_team_categories.type", "Individual")
                        ->first();

                    if ($cat_individu) {
                        if (date("Y-m-d", strtotime($cat_individu->event_start_datetime)) == $day) {
                            $cat_fix[] = $cat;
                        }
                    }
                }
            }

            $response["day"] = $sort_day;
            $response["date"] = $day;
            $response["category"] = $cat_fix;
            $response["date_format"] = dateFormatTranslate(date("l-d-F-Y", $i));
            $data[] = $response;
            $sort_day++;
        }

        return $data;
    }
}
