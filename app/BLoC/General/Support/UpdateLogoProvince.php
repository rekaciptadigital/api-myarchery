<?php

namespace App\BLoC\General\Support;

use App\Libraries\Upload;
use App\Models\City;
use DAI\Utils\Abstracts\Transactional;

class UpdateLogoCity extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $logo_base_64 = $parameters->get("logo_base_64");
        $city_id = $parameters->get("city_id");
        $logo = Upload::setPath("asset/logo_city/")
            ->setFileName("logo_city" . $city_id)
            ->setBase64($logo_base_64)
            ->save();
        $city = City::find($city_id);
        $city->logo = $logo;
        $city->save();

        return $city;
    }

    protected function validation($parameters)
    {
        return [
            "city_id" => "required|exists:cities,id",
            "logo_base_64" => "required|string"
        ];
    }
}
