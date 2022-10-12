<?php

namespace App\BLoC\Web\ArcheryCategoryDetail;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ConfigCategoryRegister;
use App\Models\ConfigSpecialCategoryMaping;
use App\Models\ConfigSpecialMaping;
use DAI\Utils\Abstracts\Transactional;

class GetConfigCategoryRegistrationDate extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get("event_id");
        $event = ArcheryEvent::find($event_id);

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

        return $response;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|integer|exists:archery_events,id",
        ];
    }
}
