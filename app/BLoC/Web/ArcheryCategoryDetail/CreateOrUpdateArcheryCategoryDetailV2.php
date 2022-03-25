<?php

namespace App\BLoC\Web\ArcheryCategoryDetail;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventMasterCompetitionCategory;
use App\Models\ArcheryMasterAgeCategory;
use App\Models\ArcheryMasterDistanceCategory;
use App\Models\ArcheryMasterTeamCategory;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class CreateOrUpdateArcheryCategoryDetailV2 extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $event = ArcheryEvent::find($parameters->get("event_id"));
        if (!$event) {
            throw new BLoCException("Event tidak ditemukan");
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException("Forbiden");
        }

        $list_category = $parameters->get("categories", []);
        if (count($list_category) == 0) {
            throw new BLoCException("harap inputkan minimal 1 kategory lomba");
        }

        foreach ($list_category as $key => $category) {
            $competitio_category = ArcheryEventMasterCompetitionCategory::find($category['competition_category_id']);
            if (!$competitio_category) {
                throw new BLoCException("Competition category tidak tersedia");
            }

            $age_category = ArcheryMasterAgeCategory::find($category['age_category_id']);
            if (!$age_category) {
                throw new BLoCException("Age category tidak tersedia");
            }

            $distance_category = ArcheryMasterDistanceCategory::find($category['distance_category_id']);
            if (!$distance_category) {
                throw new BLoCException("Distance category tidak ditemukan");
            }

            $team_category = ArcheryMasterTeamCategory::find($category['team_category_id']);
            if (!$team_category) {
                throw new BLoCException("Team category tidak ditemukan");
            }

            $date_time_event_start_register = strtotime($event->registration_start_datetime);
            $date_time_event_end_register = strtotime($event->registration_end_datetime);

            $end_early_bird = $category["end_date_early_bird"];
            $early_bird = $category["early_bird"];

            // pengecekan apakah tanggal dan harga earlybird ditentukan oleh admin
            if ($end_early_bird != null) {
                if ($early_bird == 0) {
                    throw new BLoCException("harga early bird harus lebih besar dari 0");
                }
                if (($end_early_bird < $date_time_event_start_register) && ($end_early_bird > $date_time_event_end_register)) {
                    throw new BLoCException("tanggal early bird harus berada di rentang tanggal pendaftaran event");
                }
            } elseif ($early_bird > 0) {
                if ($end_early_bird == null) {
                    throw new BLoCException("harap inputkan tanggal early bird");
                }
            }
            $key = array_key_exists("category_id", $category);
            if ($key) {
                $category_detail = ArcheryEventCategoryDetail::find($category['category_id']);
                if (!$category_detail) {
                    throw new BLoCException("kategori tidak ditemukan");
                }
                $is_exist = ArcheryEventCategoryDetail::where("age_category_id", $age_category->id)
                    ->where("competition_category_id", $competitio_category->id)
                    ->where("distance_id", $distance_category->id)
                    ->where("team_category_id", $team_category->id)
                    ->where("id", "!=", $category["category_id"])
                    ->get();

                if ($is_exist->count() > 0) {
                    throw new BLoCException("category telah di atur");
                }
                $category_detail->age_category_id = $age_category->id;
                $category_detail->competition_category_id = $competitio_category->id;
                $category_detail->distance_id  = $distance_category->id;
                $category_detail->team_category_id  = $team_category->id;
                $category_detail->quota = $category['quota'];
                $category_detail->fee = $category['fee'];
                $category_detail->early_bird = $category["early_bird"];
                $category_detail->end_date_early_bird = $end_early_bird;
                $category_detail->save();
            } else {
                $is_exist = ArcheryEventCategoryDetail::where("age_category_id", $age_category->id)
                    ->where("competition_category_id", $competitio_category->id)
                    ->where("distance_id", $distance_category->id)
                    ->where("team_category_id", $team_category->id)
                    ->get();

                if ($is_exist->count() > 0) {
                    throw new BLoCException("category telah di atur");
                }
                $archery_category_detail = new ArcheryEventCategoryDetail();
                $archery_category_detail->event_id = $event->id;
                $archery_category_detail->age_category_id = $age_category->id;
                $archery_category_detail->competition_category_id = $competitio_category->id;
                $archery_category_detail->distance_id  = $distance_category->id;
                $archery_category_detail->team_category_id  = $team_category->id;
                $archery_category_detail->quota = $category['quota'];
                $archery_category_detail->fee = $category['fee'];
                $archery_category_detail->early_bird = $category["early_bird"];
                $archery_category_detail->end_date_early_bird = $end_early_bird;
                $archery_category_detail->save();
            }
        }



        return ArcheryEvent::detailEventById($event->id);
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required',
            "categories" => "required|array|min:1",
            'categories.*.age_category_id' => 'required',
            'categories.*.competition_category_id' => 'required',
            'categories.*.distance_category_id' => 'required',
            'categories.*.team_category_id' => 'required',
            'categories.*.quota' => 'required|min:0',
            'categories.*.fee' => 'required|min:0',
            'categories.*.early_bird' => "required|min:0",
        ];
    }
}
