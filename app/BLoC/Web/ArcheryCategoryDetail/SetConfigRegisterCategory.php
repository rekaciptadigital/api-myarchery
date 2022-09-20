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
            $cwe->config_register_id = 0;
            $cwe->start_registration = $default_datetime_start_register;
            $cwe->end_registration =  $default_datetime_end_register;
            $cwe->save();
        }

        if (count($list_config) > 0) {
            foreach ($list_config as $lc) {
                if (isset($lc["id_config"]) && $lc["is_deleted"] == 1) {
                    $config = ConfigCategoryRegister::find($lc["id_config"]);
                    if (!$config) {
                        throw new BLoCException("config not found");
                    }

                    $category_with_config = ArcheryEventCategoryDetail::where("config_register_id", $config->id)->get();
                    foreach ($category_with_config as $cwc) {
                        $cwc->config_register_id = 0; // ubah config id jadi 0
                        $cwc->start_registration = $default_datetime_start_register;
                        $cwc->end_registration =  $default_datetime_end_register;
                        $cwc->save();
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

// if (count($list_config) > 0) { // cek jika ada dikirim list config
//     foreach ($list_config as $lc) {
//         // tangkap semua parameter
//         $team_category_id = $lc["team_category_id"];
//         $date_time_start_register = $lc["date_time_start_register"];
//         $date_time_end_register = $lc["date_time_end_register"];
//         $is_have_special_category = isset($lc["is_have_special_category"]) ? $lc["is_have_special_category"] : 0; // jika is_have_special tidak dikirim maka nilai default 0
//         $is_deleted = isset($lc["is_deleted"]) ? $lc["is_deleted"] : 0; // jika is_deleted tidak dikirim maka nilai default 0


//         if (isset($lc["id_config"])) { // cek jika ada config id

//             // cari config
//             $config = ConfigCategoryRegister::find($lc["id_config"]);
//             if ($config) {
//                 throw new BLoCException("config_category_register not found");
//             }

//             if ($is_deleted == 1) { //jika is deleted 1
//                 $categories = ArcheryEventCategorydetail::where("config_register_id", $config->id)->get();

//                 // isikan tanggal awal dan akhir register sesuai default diatas
//                 foreach ($categories as $c) {
//                     $c->config_register_id = 0; // ubah config id jadi 0
//                     $c->start_registration = $default_datetime_start_register;
//                     $c->end_registration =  $default_datetime_end_register;
//                     $c->save();
//                 }

//                 $config->delete();
//             } else {  // jika is deleted 0 maka update
//                 $config->team_category_id = $team_category_id;
//                 $config->date_time_start_register = $date_time_start_register;
//                 $config->date_time_end_register = $date_time_end_register;
//                 $config->save();
//             }
//         } else {
//             $config = new ConfigCategoryRegister();
//             $config->event_id = $event_id;
//             $config->team_category_id = $team_category_id;
//             $config->datetime_start_register = $date_time_start_register;
//             $config->datetime_end_register = $date_time_end_register;
//             $config->save();

//             $category_with_team_category = ArcheryEventCategoryDetail::where("team_category_id", $team_category_id)
//                 ->where("event_id", $event_id)->get();

//             foreach ($category_with_team_category as $c_w_t_c) {
//                 $c_w_t_c->config_register_id = $config->id;
//                 $c_w_t_c->start_registration = null;
//                 $c_w_t_c->end_registration =  null;
//                 $c_w_t_c->save();
//             }

//             if (
//                 $is_have_special_category == 1
//                 && isset($lc["special_category"])
//                 && count($lc["special_category"]) > 0
//             ) {
//                 foreach ($lc["special_category"] as $sc) {
//                     $category = ArcheryEventCategoryDetail::find($sc["category_id"]);
//                     if (!$category) {
//                         throw new BLoCException("category not found");
//                     }

//                     if ($sc["is_deleted"] == 1) {
//                         $category->config_register_id = 0;
//                         $category->start_registration = null;
//                         $category->end_registration =  null;
//                         $category->save();
//                     } else {
//                         $category->start_registration = $sc["date_time_start_register"];
//                         $category->end_registration =  $sc["date_time_end_register"];
//                         $category->config_register_id = $config->id;
//                         $category->save();
//                     }
//                 }
//             }
//         }
//     }
// }
