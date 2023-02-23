<?php

namespace App\BLoC\General\Support;

use App\Libraries\Upload;
use App\Models\Provinces;
use DAI\Utils\Abstracts\Transactional;

class UpdateLogoProvince extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $logo_base_64 = $parameters->get("logo_base_64");
        $province_id = $parameters->get("province_id");
        $logo = Upload::setPath("asset/logo_province/")
            ->setFileName("logo_province" . $province_id)
            ->setBase64($logo_base_64)
            ->save();
        $province = Provinces::find($province_id);
        $province->logo = $logo;
        $province->save();

        return $province;
    }

    protected function validation($parameters)
    {
        return [
            "province_id" => "required|exists:provinces,id",
            "logo_base_64" => "required|string"
        ];
    }
}
