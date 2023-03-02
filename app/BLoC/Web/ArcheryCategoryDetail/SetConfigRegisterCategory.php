<?php

namespace App\BLoC\Web\ArcheryCategoryDetail;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ClassificationEventRegisters;
use App\Models\ConfigCategoryRegister;
use App\Models\ConfigSpecialCategoryMaping;
use App\Models\ConfigSpecialMaping;
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
        $schedule_start_event = $parameters->get("schedule_start_event");
        $schedule_end_event = $parameters->get("schedule_end_event");
        $is_active_config = $parameters->get("is_active_config");
        $list_config = $parameters->get("list_config");
        $is_active_classification = $parameters->get('is_active_classification');
        $list_classification = $parameters->get("list_classification");

        // reset config
        $config = ConfigCategoryRegister::where("event_id", $event_id)->get();
        foreach ($config as $c) {
            $config_special = ConfigSpecialMaping::where("config_id", $c->id)->get();
            foreach ($config_special as $cs) {
                $config_special_category = ConfigSpecialCategoryMaping::where("special_config_id", $cs->id)->get();
                foreach ($config_special_category as $csc) {
                    $csc->delete();
                }
                $cs->delete();
            }
            $c->delete();
        }

        $list_category = ArcheryEventCategoryDetail::where("event_id", $event_id)->get();
        foreach ($list_category as $lc) {
            $lc->start_registration = null;
            $lc->end_registration = null;
            $lc->save();
        }
        // akhir reset config

        // reset classification
        ClassificationEventRegisters::where("event_id", '=', $event_id)->delete();


        // set ulang tanggal pendaftaran event
        $event = ArcheryEvent::find($event_id);

        // if (strtotime($default_datetime_start_register) < time()) {
        //     throw new BLoCException("event start register invalid");
        // }

        if (strtotime($default_datetime_end_register) <= strtotime($default_datetime_start_register)) {
            throw new BLoCException("tanggal mulai registrasi harus sebelum tanggal akhir registrasi");
        }

        if (strtotime($schedule_end_event) <= strtotime($schedule_start_event)) {
            throw new BLoCException("tanggal mulai event harus sebelum tanggal akhir event");
        }

        if (strtotime($default_datetime_end_register) > strtotime("-1 day", strtotime($schedule_start_event))) {
            throw new BLoCException("event end register harus h-1 sebelum mulai event");
        }


        $event->registration_start_datetime = $default_datetime_start_register;
        $event->registration_end_datetime = $default_datetime_end_register;
        $event->event_start_datetime = $schedule_start_event;
        $event->event_end_datetime = $schedule_end_event;
        $event->save();

        // set ulang tanggal pendaftaran per kategori
        $category_where_event = ArcheryEventCategoryDetail::where("event_id", $event_id)->get();
        foreach ($category_where_event as $cwe) {
            $cwe->start_registration = $default_datetime_start_register;
            $cwe->end_registration =  $default_datetime_end_register;
            $cwe->save();
        }

        // akhir set ulang pendaftaran per kategori

        if ($is_active_config == 1) {
            foreach ($list_config as $lc) {

                if (isset($lc["date_time_start_register_config"]) && isset($lc["date_time_end_register_config"])) {
                    if (strtotime($lc["date_time_start_register_config"]) >= strtotime($lc["date_time_end_register_config"])) {
                        throw new BLoCException("tanggal mulai registrasi harus sebelum tanggal akhir registrasi");
                    }

                    if (strtotime($lc["date_time_end_register_config"]) > strtotime("-1 day", strtotime($event->event_start_datetime))) {
                        throw new BLoCException("event end register harus h-1 sebelum mulai event");
                    }
                }



                $config = new ConfigCategoryRegister();
                $config->event_id = $event_id;
                $config->config_type = $lc["config_type"];
                $config->is_have_special = $lc["is_have_special_category"];
                $config->datetime_start_register = isset($lc["date_time_start_register_config"]) ? $lc["date_time_start_register_config"] : $default_datetime_start_register;
                $config->datetime_end_register = isset($lc["date_time_end_register_config"]) ? $lc["date_time_end_register_config"] : $default_datetime_end_register;
                $config->save();

                if ($config->is_have_special == 1) {
                    foreach ($lc["special_category"] as $sc) {
                        if (!isset($sc["list_category"]) || count($sc["list_category"]) == 0) {
                            throw new BLoCException("category harus diisi");
                        }

                        if (isset($sc["date_time_start_register_special_category"]) && isset($sc["date_time_end_register_special_category"])) {
                            if (strtotime($sc["date_time_start_register_special_category"]) >= strtotime($sc["date_time_end_register_special_category"])) {
                                throw new BLoCException("tanggal mulai registrasi harus sebelum tanggal akhir registrasi");
                            }

                            if (strtotime($sc["date_time_end_register_special_category"]) > strtotime("-1 day", strtotime($event->event_start_datetime))) {
                                throw new BLoCException("event end register harus h-1 sebelum mulai event");
                            }
                        } else {
                            throw new BLoCException("event start register dan event end register harus diiisi");
                        }

                        $config_special = new ConfigSpecialMaping();
                        $config_special->config_id = $config->id;
                        $config_special->datetime_start_register = $sc["date_time_start_register_special_category"];
                        $config_special->datetime_end_register = $sc["date_time_end_register_special_category"];
                        $config_special->save();

                        foreach ($sc["list_category"] as $lcs) {
                            $category = ArcheryEventCategoryDetail::find($lcs["category_id"]);
                            if (!$category) {
                                throw new BLoCException("category not found");
                            }
                            $special_category_mapping = new ConfigSpecialCategoryMaping();
                            $special_category_mapping->special_config_id = $config_special->id;
                            $special_category_mapping->category_id = $lcs["category_id"];
                            $special_category_mapping->save();

                            $category->start_registration = $config_special->datetime_start_register;
                            $category->end_registration = $config_special->datetime_end_register;
                            $category->save();
                        }
                    }
                } else {
                    if ($config->config_type == 1) {
                        $list_category = ArcheryEventCategoryDetail::where("event_id", $event->id)
                            ->where(function ($q) {
                                $q->where("team_category_id", "individu male")
                                    ->orWhere("team_category_id", "individu female");
                            })
                            ->get();
                    } elseif ($config->config_type == 2) {
                        $list_category = ArcheryEventCategoryDetail::where("event_id", $event->id)
                            ->where("team_category_id", "individu_mix")
                            ->get();
                    } elseif ($config->config_type == 3) {
                        $list_category = ArcheryEventCategoryDetail::where("event_id", $event->id)
                            ->where(function ($q) {
                                $q->where("team_category_id", "male_team")
                                    ->orWhere("team_category_id", "female_team");
                            })
                            ->get();
                    } else {
                        $list_category = ArcheryEventCategoryDetail::where("event_id", $event->id)
                            ->where("team_category_id", "mix_team")
                            ->get();
                    }

                    foreach ($list_category as $lc1) {
                        $lc1->start_registration = $config->datetime_start_register;
                        $lc1->end_registration = $config->datetime_end_register;
                        $lc1->save();
                    }
                }
            }
        }

        if ($is_active_classification == 1) {
            $data_map_classification = [];
            foreach ($list_classification as $key => $value) {
                $set_data_classification = [
                    'parent_classification_id' => $value['parent_classification_id'],
                    'event_id' => $event_id,
                    'children_classification_id' => null,
                    'country_id' => null,
                    'states_id' => null,
                    'city_of_contry_id' => null,
                    'archery_club_id' => null
                ];

                if (empty($value['parent_classification_id'])) throw new BLoCException("parent klasifikasi wajib di isi!");


                switch ($value['parent_classification_id']) {
                    case 1:
                        if (empty($value['archery_club_id'])) {
                            throw new BLoCException("jika memilih parent klasifikasi klub maka klub wajib di pilih!");
                        } else {
                            $set_data_classification['archery_club_id'] = $value['archery_club_id'];
                        }
                        break;
                    case 2:
                        if (empty($value['country_id'])) {
                            throw new BLoCException("jika memilih parent klasifikasi negara maka negara wajib di pilih!");
                        } else {
                            $set_data_classification['country_id'] = $value['country_id'];
                        }
                        break;
                    case 3:
                        if (empty($value['country_id']) || empty($value['states_id'])) {
                            throw new BLoCException("jika memilih parent klasifikasi provinsi maka negara, provinsi wajib di pilih!");
                        } else {
                            $set_data_classification['country_id'] = $value['country_id'];
                            $set_data_classification['states_id'] = $value['states_id'];
                        }
                        break;
                    case 4:
                        if (empty($value['country_id']) || empty($value['states_id']) || empty($value['city_of_contry_id'])) {
                            throw new BLoCException("jika memilih parent klasifikasi kota maka negara, provinsi, kota wajib di pilih!");
                        } else {
                            $set_data_classification['country_id'] = $value['country_id'];
                            $set_data_classification['states_id'] = $value['states_id'];
                            $set_data_classification['city_of_contry_id'] = $value['city_of_contry_id'];
                        }
                        break;
                    default:
                        if (empty($value['children_classification_id'])) {
                            throw new BLoCException("jika memilih parent klasifikasi custom maka wajib di isi!");
                        } else {
                            $set_data_classification['children_classification_id'] = $value['children_classification_id'];
                        }
                        break;
                }

                array_push($data_map_classification, $set_data_classification);
            }

            ClassificationEventRegisters::insert($data_map_classification);
        }

        // susun response
        $response = [];
        $response["event_id"] = $event->id;
        $response["default_datetime_register"] = [
            "start" => $event->registration_start_datetime,
            "end" => $event->registration_end_datetime
        ];

        $response["schedule_event"] = [
            "start" => $event->event_start_datetime,
            "end" => $event->event_end_datetime,
        ];

        $config = ConfigCategoryRegister::where("event_id", $event_id)->get();
        $enable_config = 0;
        if ($config->count() > 0) {
            $enable_config = 1;
        }

        $response["enable_config"] = $enable_config;

        foreach ($config as $c) {
            if ($c->is_have_special == 1) {
                $list_special_config = [];
                $config_special_mapping = ConfigSpecialMaping::where("config_id", $c->id)->get();
                foreach ($config_special_mapping as $csm) {
                    $categories = [];
                    $config_special_category_mapping = ConfigSpecialCategoryMaping::where("special_config_id", $csm->id)->get();
                    foreach ($config_special_category_mapping as $cscm) {
                        $label = ArcheryEventCategoryDetail::getCategoryLabelComplete($cscm->category_id);
                        $cscm->label = $label;
                        $categories[] = $cscm;
                    }
                    $csm->categories = $categories;
                    $list_special_config[] = $csm;
                }
                $c->list_special_config = $list_special_config;
            }
            $response["list_config"][] = $c;
        }

        $get_classification = ClassificationEventRegisters::where('event_id', '=', $event_id)
            ->where('deleted_at', '=', null)
            ->get();

        $enable_classification = 0;
        if ($get_classification->count() > 0) {
            $enable_classification = 1;
        }

        $response['is_active_classification'] = $enable_classification;
        $response['list_classification'] = $get_classification;

        return $response;
    }

    protected function validation($parameters)
    {
        $rules = [
            "event_id" => "required|integer|exists:archery_events,id",
            "default_datetime_start_register" => "required|string",
            "default_datetime_end_register" => "required|string",
            "is_active_config" => "required|integer|in:1,0",
            "schedule_start_event" => "required|string",
            "schedule_end_event" => "required|string",
        ];

        if ($parameters->get("is_active_config") == 1) {
            $rules["list_config"] = "required|array";
            $rules["list_config.*.config_type"] = "required|integer|in:1,2,3,4";
            $rules["list_config.*.is_have_special_category"] = "in:0,1";
        }


        return $rules;
    }
}
