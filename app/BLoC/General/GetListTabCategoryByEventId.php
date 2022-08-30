<?php

namespace App\BLoC\General;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryMasterAgeCategory;
use App\Models\ArcheryMasterCompetitionCategory;
use App\Models\ArcheryMasterDistanceCategory;
use App\Models\GroupCategory;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class GetListTabCategoryByEventId extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $rating_flag = [1, 2, 3];
        $categories = ArcheryEventCategoryDetail::select("archery_event_category_details.*")->where("event_id", $parameters->get("event_id"))
            ->join("archery_master_age_categories", "archery_master_age_categories.id", "=", "archery_event_category_details.age_category_id")
            ->distinct()
            ->get();

        $data = [];
        foreach ($rating_flag as $rf) {
            $categories_group = [];
            foreach ($categories as $c) {
                if ($c->rating_flag == $rf) {
                    $group_category_name = "";

                    if ($c->rating_flag == 1) {
                        $response_category = [];
                        $is_exist = false;
                        foreach ($data as $d) {
                            if (isset($d["type"]) && $d["type"] == 1) {
                                $is_exist =  true;
                            }
                        }
                        if ($is_exist == true) {
                            continue;
                        }
                        $group_category_name = "semua kategori";
                        $response_category["type"] = 1;
                        $response_category["label"] = $group_category_name;
                        $response_category["param_request_rank"] = [
                            "rules_rating_club" => $c->rules_rating_club,
                        ];
                    }

                    if ($c->rating_flag == 2) {
                        $response_category = [];
                        $is_exist_group = false;
                        foreach ($data as $d) {
                            if ((isset($d["param_request_rank"]["age_category_id"]) && $d["param_request_rank"]["age_category_id"] == $c->age_category_id)
                                && (isset($d["param_request_rank"]["competition_category_id"]) && $d["param_request_rank"]["competition_category_id"] == $c->competition_category_id)
                                && (isset($d["param_request_rank"]["distance_id"]) && $d["param_request_rank"]["distance_id"] == $c->distance_id)
                            ) {
                                $is_exist_group = true;
                            }
                        }
                        if ($is_exist_group == true) {
                            continue;
                        }
                        $age_category = ArcheryMasterAgeCategory::find($c->age_category_id);
                        $competition_category = ArcheryMasterCompetitionCategory::find($c->competition_category_id);
                        $distance = ArcheryMasterDistanceCategory::find($c->distance_id);
                        $group_category_name = $competition_category->label . " - " . $age_category->label . " - " . $distance->label;

                        $response_category["type"] = 2;
                        $response_category["label"] = $group_category_name;
                        $response_category["param_request_rank"] = [
                            "age_category_id" => $c->age_category_id,
                            "competition_category_id" => $c->competition_category_id,
                            "distance_id" => $c->distance_id,
                            "rules_rating_club" => $c->rules_rating_club
                        ];
                    }


                    if ($c->rating_flag == 3) {
                        $response_category = [];
                        foreach ($data as $d) {
                            $is_exist_group_3 = false;
                            if (isset($d["type"]) && $d["type"] == 3) {
                                $is_exist_group_3 =  true;
                            }
                        }

                        if ($is_exist_group_3 == true) {
                            continue;
                        }
                        $group_category = GroupCategory::find($c->group_category_id);
                        if (!$group_category) {
                            throw new BLoCException("group category not found");
                        }
                        $group_category_name = $group_category->group_category_name;
                        $response_category["type"] = 3;
                        $response_category["label"] = $group_category_name;
                        $response_category["param_request_rank"] = [
                            "group_category_id" => $group_category->id,
                            "rules_rating_club" => $c->rules_rating_club
                        ];
                    }

                    $data[] = $response_category;
                }
            }
        }

        return $data;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|integer|exists:archery_events,id"
        ];
    }
}
