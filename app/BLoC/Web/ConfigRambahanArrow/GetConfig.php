<?php

namespace App\BLoC\Web\ConfigRambahanArrow;

use App\Models\ArcheryEvent;
use App\Models\ArcheryMasterAgeCategory;
use App\Models\ArcheryMasterCompetitionCategory;
use App\Models\ArcheryMasterDistanceCategory;
use App\Models\CategoryConfig;
use App\Models\CategoryConfigMappingArrowRambahan;
use App\Models\ConfigArrowRambahan;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetConfig extends Transactional
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



        $event = ArcheryEvent::find($event_id);

        // cek pemilik event
        if ($event->admin_id != $admin->id) {
            throw new BLoCException("you are not owner this event");
        }

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
        return [
            "event_id" => "required|integer|exists:archery_events,id",
        ];
    }
}
