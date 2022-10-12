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
        $implement_all  = $parameters->get("implement_all");
        $session = $parameters->get("session");
        $rambahan = $parameters->get("rambahan");
        $child_bow = $parameters->get("child_bow");
        $shoot_rule  = $parameters->get("shoot_rule");


        $event = ArcheryEvent::find($event_id);

        // cek pemilik event
        if ($event->admin_id != $admin->id) {
            throw new BLoCException("you are not owner this event");
        }

        // reset config
        $list_category = ArcheryEventCategoryDetail::where("event_id", $event_id)->get(); // tangkap semua kategori

        // ubah jumlah arrow dan rambahan di semua kategori menjadi nilai default
        foreach ($list_category as $category) {
            $category->count_stage = 6;
            $category->count_shot_in_stage = 6;
            $category->session_in_qualification = 2;
            $category->save();
        }

        // hapus semua kategori config
        $config = ConfigArrowRambahan::where("event_id", $event_id)->first();

        if ($config) {
            if ($config->type == 2) { // jika config type untuk kategori khusus
                $list_category_config = CategoryConfig::where("config_arrow_rambahan_id", $config->id)->get(); // tangkap semua config list_category khusus
                // delete semua config categori khusus
                foreach ($list_category_config as $category_config) {
                    $list_category_2 = CategoryConfigMappingArrowRambahan::where("config_category_id", $category_config->id)->get(); // tangkap semua kategori yang ada
                    // delete semua category
                    foreach ($list_category_2 as $category_2) {
                        $category_2->delete();
                    }
                    $category_config->delete(); // delete category_config
                }
            }

            $config->delete(); // delete config
        }


        // set ulang
        if ($active_setting == 1) {
            if ($implement_all == 1) {
                $config = new ConfigArrowRambahan();
                $config->event_id = $event->id;
                $config->type = 1;
                $config->session = $session;
                $config->arrow = $child_bow;
                $config->rambahan = $rambahan;
                $config->save();
                $list_category_3 = ArcheryEventCategoryDetail::where("event_id", $event->id)->get();
                foreach ($list_category_3 as $category_3) {
                    $category_3->session_in_qualification = $session;
                    $category_3->count_shot_in_stage = $child_bow;
                    $category_3->count_stage = $rambahan;
                    $category_3->save();
                }
            } else {
                $config = new ConfigArrowRambahan();
                $config->event_id = $event->id;
                $config->type = 2;
                $config->session = 6;
                $config->arrow = 6;
                $config->rambahan = 2;
                $config->save();
                if (count($shoot_rule) > 0) {
                    foreach ($shoot_rule as $r) {
                        $category_config = new CategoryConfig();
                        $category_config->session = $r["session"];
                        $category_config->arrow = $r["child_bow"];
                        $category_config->rambahan = $r["rambahan"];
                        $category_config->config_arrow_rambahan_id = $config->id;
                        $category_config->save();
                        foreach ($r["category"] as $c) {
                            $competition = ArcheryMasterCompetitionCategory::find($c["competition_category_id"]);
                            if (!$competition) {
                                throw new BLoCException("competion not found");
                            }

                            $distnace = ArcheryMasterDistanceCategory::find($c["distance_id"]);
                            if (!$distnace) {
                                throw new BLoCException("distance not found");
                            }

                            $age_category = ArcheryMasterAgeCategory::find($c["age_category_id"]);
                            if (!$age_category) {
                                throw new BLoCException("age not found");
                            }

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
                                ->get();

                            foreach ($list_category_4 as $c4) {
                                $c4->session_in_qualification = $r["session"];
                                $c4->count_shot_in_stage = $r["child_bow"];
                                $c4->count_stage = $r["rambahan"];
                                $c4->save();
                            }
                        }
                    }
                }
            }
        }

        // susun response
        $response = [];
        $active_setting_1 = 0;
        $implement_all_1 = 1;
        $session_1 = 2;
        $arrow_1 = 6;
        $rambahan_1 = 6;
        $shoot_rule_1 = null;
        $response["event_id"] = $event->id;
        $config = ConfigArrowRambahan::where("event_id", $event_id)->first();

        if ($config) {
            $active_setting_1 = 1;
            if ($config->type == 2) {
                $implement_all_1 = 0;
                $list_special_config = [];
                $list_category_config = CategoryConfig::where("config_arrow_rambahan_id", $config->id)->get(); // tangkap semua config list_category khusus 

                foreach ($list_category_config as $category_config) {
                    $categories = [];
                    $list_category_2 = CategoryConfigMappingArrowRambahan::where("config_category_id", $category_config->id)->get();
                    foreach ($list_category_2 as $category_2) {
                        $competition = ArcheryMasterCompetitionCategory::find($category_2->competition_category_id);
                        if (!$competition) {
                            throw new BLoCException("competion not found");
                        }

                        $distnace = ArcheryMasterDistanceCategory::find($category_2->distance_id);
                        if (!$distnace) {
                            throw new BLoCException("distance not found");
                        }

                        $age_category = ArcheryMasterAgeCategory::find($category_2->age_category_id);
                        if (!$age_category) {
                            throw new BLoCException("age not found");
                        }

                        $category_2->label = $competition->label . "-" . $age_category->label . "-" . $distnace->label;
                        $categories[] = $category_2;
                    }

                    $category_config->categories = $categories;
                    $list_special_config[] = $category_config;
                }

                $shoot_rule_1 = $list_special_config;
            } else {
                $session_1 = $config->session;
                $arrow_1 = $config->arrow;
                $rambahan_1 = $config->rambahan;
            }
        }

        $response["active_setting"] = $active_setting_1;
        $response["implement_all"] = $implement_all_1;
        if ($implement_all_1 == 1) {
            $response["session"] = $session_1;
            $response["child_bow"] = $arrow_1;
            $response["rambahan"] = $rambahan_1;
        }
        $response["shoot_rule"] = $shoot_rule_1;

        return $response;
    }

    protected function validation($parameters)
    {
        $rules = [
            "event_id" => "required|integer|exists:archery_events,id",
            "active_setting" => "required|in:0,1",
            "implement_all" => "required|in:0,1",
        ];


        $implement_all  = $parameters->get("implement_all");

        if ($implement_all == 1) {
            $rules["session"] = "required|numeric|min:1";
            $rules["rambahan"] = "required|numeric|min:3|max:15";
            $rules["child_bow"] = "required|numeric|min:3|max:15";
        } else {
            $rules["shoot_rule"] = "required|array:min:1";
            $rules["shoot_rule.*.session"] = "required|numeric|min:1";
            $rules["shoot_rule.*.rambahan"] = "required|numeric|min:3|max:15";
            $rules["shoot_rule.*.child_bow"] = "required|numeric|min:3|max:15";
            $rules["shoot_rule.*.category"] = "required|array|min:1";
            $rules["shoot_rule.*.category"] = "required|array|min:1";
            $rules["shoot_rule.*.category.*.competition_category_id"] = "required|exists:archery_master_competition_categories,id";
            $rules["shoot_rule.*.category.*.age_category_id"] = "required|exists:archery_master_age_categories,id";
            $rules["shoot_rule.*.category.*.distance_id"] = "required|exists:archery_master_distances,id";
        }

        return $rules;
    }
}
