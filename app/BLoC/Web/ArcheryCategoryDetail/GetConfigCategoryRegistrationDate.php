<?php

namespace App\BLoC\Web\ArcheryCategoryDetail;

use App\Models\ConfigCategoryRegister;
use DAI\Utils\Abstracts\Transactional;

class GetConfigCategoryRegistrationDate extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        // $event_id = $parameters->get("event_id");
        // $config = ConfigCategoryRegister::where("event_id", $event_id)->get();
        // foreach ($config as $key => $c) {
            
        // }
    }

    protected function validation($parameters)
    {
        return [];
    }
}
