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
        // tangkap parameter
        $event_id = $parameters->get("event_id");
        $default_datetime_start_register = $parameters->get("default_datetime_start_register");
        $default_datetime_end_register = $parameters->get("default_datetime_end_register");
        $list_config = $parameters->get("list_config");

        // set ulang tanggal pendaftaran event
        $event = ArcheryEvent::find($event_id);
        $event->registration_start_datetime = $default_datetime_start_register;
        $event->registration_end_datetime = $default_datetime_end_register;
        $event->save();

        // set ulang tanggal pendaftaran per kategori
        $category_where_event = ArcheryEventCategoryDetail::where("event_id", $event_id)->get();
        foreach ($category_where_event as $cwe) {
            $cwe->is_special_cat_config = 0;
            $cwe->config_register_id = 0;
            $cwe->start_registration = $default_datetime_start_register;
            $cwe->end_registration =  $default_datetime_end_register;
            $cwe->save();
        }

        if (count($list_config) > 0) {
            foreach ($list_config as $lc) {
                if (isset($lc["id_config"])) {
                    $config = ConfigCategoryRegister::find($lc["id_config"]);
                    if (!$config) {
                        throw new BLoCException("config not found");
                    }
                    if ($lc["is_deleted"] == 1) {
                        $category_with_config = ArcheryEventCategoryDetail::where("config_register_id", $config->id)->get();
                        foreach ($category_with_config as $cwc) {
                            $cwc->config_register_id = 0; // ubah config id jadi 0
                            $cwc->start_registration = $default_datetime_start_register;
                            $cwc->end_registration =  $default_datetime_end_register;
                            $cwc->save();
                        }
                    } else {
                        $config->team_category_id = $lc["team_category_id"];
                        $config->date_time_start_register = $lc["date_time_start_register"];
                        $config->date_time_end_register = $lc["date_time_end_register"];
                        $config->is_have_special = $lc["is_have_special_category"];
                        $config->save();

                        $category_with_config = ArcheryEventCategoryDetail::where("config_register_id", $config->id)->get();
                        foreach ($category_with_config as $cwc) {
                            $cwc->config_register_id = $config->id;
                            $cwc->start_registration = $config->date_time_start_register;
                            $cwc->end_registration =  $config->date_time_end_register;
                            $cwc->save();
                        }

                        if (
                            $lc["is_have_special_category"] == 1
                            && isset($lc["special_category"])
                            && count($lc["special_category"]) > 0
                        ) {
                            foreach ($lc["special_category"] as $sc) {
                                $category_sc = ArcheryEventCategoryDetail::find($sc["category_id"]);
                                if (!$category_sc) {
                                    throw new BLoCException("category not found");
                                }
                                if ($sc["is_deleted"] == 1) {
                                    $category_sc->config_register_id = 0;
                                    $category_sc->is_special_cat_config = 0;
                                    $category_sc->start_registration = $default_datetime_start_register;
                                    $category_sc->end_registration = $default_datetime_end_register;
                                    $category_sc->save();
                                } else {
                                    $category_sc->config_register_id = $config->id;
                                    $category_sc->is_special_cat_config = 1;
                                    $category_sc->start_registration = $sc["date_time_start_register"];
                                    $category_sc->end_registration = $sc["date_time_end_register"];
                                    $category_sc->save();
                                }
                            }
                        } else {
                            $category_sp = ArcheryEventCategoryDetail::where("event_id", $event_id)
                                ->where("config_register_id", $config->id)
                                ->where("is_special", 1)
                                ->get();

                            foreach ($category_sp as $csp) {
                                $csp->config_register_id = 0;
                                $csp->is_special_cat_config = 0;
                                $csp->start_registration = $default_datetime_start_register;
                                $csp->end_registration = $default_datetime_end_register;
                                $csp->save();
                            }
                        }
                    }
                } else {
                    $config = new ConfigCategoryRegister();
                    $config->event_id = $event_id;
                    $config->is_have_special = $lc["is_have_special_category"];
                    $config->team_category_id = $lc["team_category_id"];
                    $config->datetime_start_register = $lc["date_time_start_register"];
                    $config->datetime_end_register = $lc["date_time_end_register"];
                    $config->save();

                    $category_where_team_cat = ArcheryEventCategoryDetail::where("event_id", $event_id)->where("team_category_id", $lc["team_category_id"])->get();
                    foreach ($category_where_team_cat as $cwtc) {
                        $cwtc->is_special_cat_config = 0;
                        $cwtc->config_register_id = 0;
                        $cwtc->start_registration = $default_datetime_start_register;
                        $cwtc->end_registration =  $default_datetime_end_register;
                        $cwtc->save();
                    }

                    if (
                        $lc["is_have_special_category"] == 1
                        && isset($lc["special_category"])
                        && count($lc["special_category"]) > 0
                    ) {
                        foreach ($lc["special_category"] as $sc) {
                            $category_sc = ArcheryEventCategoryDetail::find($sc["category_id"]);
                            if (!$category_sc) {
                                throw new BLoCException("category not found");
                            }

                            $category_sc->config_register_id = $config->id;
                            $category_sc->is_special_cat_config = 1;
                            $category_sc->start_registration = $sc["date_time_start_register"];
                            $category_sc->end_registration = $sc["date_time_end_register"];
                            $category_sc->save();
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
            "default_datetime_start_register" => "required|string",
            "default_datetime_end_register" => "required|string",
            "list_config" => "required|array",
        ];

        if ($parameters->get("list_config") != null && count($parameters->get("list_config")) > 0) {
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
