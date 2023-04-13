<?php

namespace App\BLoC\Web\ConfigRambahanArrow;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryMasterAgeCategory;
use App\Models\ArcheryMasterCompetitionCategory;
use App\Models\ArcheryMasterDistanceCategory;
use App\Models\CategoryConfig;
use App\Models\CategoryConfigMappingArrowRambahan;
use App\Models\ConfigArrowRambahan;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class SetConfig extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user(); // admin admin login

        // tangkap semua params
        $event_id = $parameters->get('event_id');
        $active_setting  = $parameters->get("active_setting");
        $shoot_rule  = $parameters->get("shoot_rule");


        $event = ArcheryEvent::find($event_id);

        // cek pemilik event
        if ($event->admin_id != $admin->id) {
            throw new BLoCException("you are not owner this event");
        }


        // reset config
        ConfigArrowRambahan::resetConfigArrowRambahan($event);


        // set ulang
        if ($active_setting == 1) {
            $config = new ConfigArrowRambahan();
            $config->event_id = $event->id;
            $config->type = count($shoot_rule) > 0 ? 2 : 1;
            $config->save();

            if (count($shoot_rule) > 0) {
                foreach ($shoot_rule as $r) {
                    $category_config = new CategoryConfig();
                    $category_config->session = $r["session"];
                    $category_config->arrow = $r["child_bow"];
                    $category_config->rambahan = $r["rambahan"];
                    $category_config->have_special_category = $r["have_special_category"];
                    $category_config->config_arrow_rambahan_id = $config->id;
                    $category_config->save();

                    if ($category_config->have_special_category == 1) {
                        if (!isset($r["category"]) || count($r["category"]) == 0) {
                            throw new BLoCException("category harus diisi");
                        }

                        foreach ($r["category"] as $c) {
                            $competition = ArcheryMasterCompetitionCategory::find($c["competition_category_id"]);
                            $distnace = ArcheryMasterDistanceCategory::find($c["distance_id"]);
                            $age_category = ArcheryMasterAgeCategory::find($c["age_category_id"]);

                            $CategoryConfigMappingArrowRambahan = new CategoryConfigMappingArrowRambahan();
                            $CategoryConfigMappingArrowRambahan->config_category_id = $category_config->id;
                            $CategoryConfigMappingArrowRambahan->competition_category_id = $competition->id;
                            $CategoryConfigMappingArrowRambahan->age_category_id = $age_category->id;
                            $CategoryConfigMappingArrowRambahan->distance_id = $distnace->id;
                            $CategoryConfigMappingArrowRambahan->save();

                            $list_category_4 = ArcheryEventCategoryDetail::where("competition_category_id", $competition->id)
                                ->where("age_category_id", $age_category->id)
                                ->where("distance_id", $distnace->id)
                                ->where("event_id", $event->id)
                                ->get();;

                            foreach ($list_category_4 as $c4) {
                                $c4->session_in_qualification = $category_config->session;
                                $c4->count_shot_in_stage = $category_config->arrow;
                                $c4->count_stage = $category_config->rambahan;
                                $c4->save();
                            }
                        }
                    } else {
                        if (isset($r["category"]) && count($r["category"]) > 0) {
                            throw new BLoCException("category harus dikosongkan");
                        }
                        $list_category = ArcheryEventCategoryDetail::where("event_id", $event->id)
                            ->get();

                        foreach ($list_category as $key => $category) {
                            $CategoryConfigMappingArrowRambahan = CategoryConfigMappingArrowRambahan::select("category_config_mapping_arrow_rambahan.*")
                                ->join(
                                    "category_config",
                                    "category_config.id",
                                    "=",
                                    "category_config_mapping_arrow_rambahan.config_category_id"
                                )->join(
                                    "config_arrow_rambahan",
                                    "config_arrow_rambahan.id",
                                    "=",
                                    "category_config.config_arrow_rambahan_id"
                                )
                                ->where("config_arrow_rambahan.event_id", $event->id)
                                ->get();

                            if ($CategoryConfigMappingArrowRambahan->count() > 0) {
                                foreach ($CategoryConfigMappingArrowRambahan as $value) {
                                    if (
                                        $category->event_id == $event->id
                                        && $category->competition_category_id == $value->competition_category_id
                                        && $category->age_category_id == $value->age_category_id
                                        && $category->distance_id == $value->distance_id
                                    ) {
                                        unset($list_category[$key]);
                                    }
                                }
                            }
                        }

                        foreach ($list_category as $category) {
                            $category->session_in_qualification = $category_config->session;
                            $category->count_shot_in_stage = $category_config->arrow;
                            $category->count_stage = $category_config->rambahan;
                            $category->save();
                        }
                    }
                }
            }
        }

        return "success";
    }

    protected function validation($parameters)
    {
        $rules = [
            "event_id" => "required|integer|exists:archery_events,id",
            "active_setting" => "required|in:0,1",
        ];

        $active_setting = $parameters->get("active_setting");

        if ($active_setting == 1) {
            $rules["shoot_rule"] = "required|array:min:1";
            $rules["shoot_rule.*.session"] = "required|numeric|min:1";
            $rules["shoot_rule.*.rambahan"] = "required|numeric|min:3|max:15";
            $rules["shoot_rule.*.child_bow"] = "required|numeric|min:3|max:15";
            $rules["shoot_rule.*.have_special_category"] = "required|in:0,1";
        }

        $rules["shoot_rule.*.category"] = "array";
        $rules["shoot_rule.*.category.*.competition_category_id"] = "exists:archery_master_competition_categories,id";
        $rules["shoot_rule.*.category.*.age_category_id"] = "exists:archery_master_age_categories,id";
        $rules["shoot_rule.*.category.*.distance_id"] = "exists:archery_master_distances,id";


        return $rules;
    }
}
