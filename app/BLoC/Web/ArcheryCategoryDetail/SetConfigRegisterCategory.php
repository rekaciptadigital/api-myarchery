<?php

namespace App\BLoC\Web\ArcheryCategoryDetail;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ConfigCategoryRegister;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;

class SetConfigRegisterCategory extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get("event_id");
        $default_datetime_start_register = $parameters->get("default_datetime_start_register");
        $default_datetime_end_register = $parameters->get("default_datetime_end_register");
        $list_config = $parameters->get("list_config");

        $event = ArcheryEvent::find($event_id);
        $event->registration_start_datetime = $default_datetime_start_register;
        $event->registration_end_datetime = $default_datetime_end_register;
        $event->save();
        
        if (count($list_config) > 0) {
            foreach ($list_config as $lc) {
                $team_category_id = $lc["team_category_id"];
                $date_time_start_register = $lc["date_time_start_register"];
                $date_time_end_register = $lc["date_time_end_register"];
                $is_have_special_category = isset($lc["is_have_special_category"]) ? $lc["is_have_special_category"] : 0;
                $is_deleted = isset($lc["is_deleted"]) ? $lc["is_deleted"] : 0;


                if (isset($lc["id_config"])) {

                    $config = ConfigCategoryRegister::find($lc["id_config"]);
                    if ($config) {
                        throw new BLoCException("config_category_register not found");
                    }

                    if ($is_deleted == 1) {
                        $categories = ArcheryEventCategorydetail::where("config_register_id", $config->id)->get();

                        foreach ($categories as $c) {
                            $c->config_register_id = 0;
                            $c->save();
                        }

                        $config->delete();
                    } else {
                        $config->team_category_id = $team_category_id;
                        $config->date_time_start_register = $date_time_start_register;
                        $config->date_time_end_register = $date_time_end_register;
                        $config->save();
                    }
                } else {
                    $config = new ConfigCategoryRegister();
                    $config->event_id = $event_id;
                    $config->team_category_id = $team_category_id;
                    $config->datetime_start_register = $date_time_start_register;
                    $config->datetime_end_register = $date_time_end_register;
                    $config->save();

                    if (
                        $is_have_special_category == 1
                        && isset($lc["special_category"])
                        && count($lc["special_category"]) > 0
                    ) {
                        foreach ($lc["special_category"] as $sc) {
                            $category = ArcheryEventCategoryDetail::find($sc["category_id"]);
                            if (!$category) {
                                throw new BLoCException("category not found");
                            }
                            $category->config_register_id = $config->id;
                            $category->save();
                        }
                    }
                }
            }
        }
    }

    protected function validation($parameters)
    {
        $rules = [
            "event_id" => "required|integer|exists:archery_events,id",
            "default_datetime_start_register" => "required|string",
            "default_datetime_end_register" => "required|string",
            "list_config" => "array",
        ];

        if (count($parameters->get("list_config")) > 0) {
            $rules["list_config.*.team_category_id"] = "required|string|exists:archery_master_team_categories,id";
            $rules["list_config.*.date_time_start_register"] = "required|string";
            $rules["list_config.*.date_time_end_register"] = "required|string";
            $rules["list_config.*.is_have_special_category"] = "in:0,1";
            $rules["list_config.*.is_deleted"] = "in:0,1";
            $rules["list_config.*.id_config"] = "exists:config_category_registers,id";
        }

        return $rules;
    }
}
