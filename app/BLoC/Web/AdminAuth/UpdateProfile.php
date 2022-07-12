<?php

namespace App\BLoC\Web\AdminAuth;

use App\Models\Admin;
use Illuminate\Validation\Rule;
use App\Models\City;
use App\Models\Provinces;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class UpdateProfile extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin_login = Auth::user();
        $province_id = $parameters->get("province_id");
        $city_id = $parameters->get("city_id");
        $admin_id = $parameters->get("admin_id");

        if (isset($province_id)) {
            $province = Provinces::find($province_id);
            if (!$province) {
                throw new BLoCException("province not found");
            }
        }

        if (isset($province_id) && isset($city_id)) {
            $city = City::where("province_id", $province_id)->where("id", $city_id)->first();
            if (!$city) {
                throw new BLoCException("city not valid");
            }
        }

        if ($admin_login->id != $admin_id) {
            throw new BLoCException("forbiden");
        }

        $admin = Admin::find($admin_id);
        if (!$admin) {
            throw new BLoCException("admin not found");
        }

        $admin->name = $parameters->get('name_organizer');
        $admin->province_id = $province_id;
        $admin->city_id = $city_id;
        $admin->phone_number = $parameters->get('phone_number');
        $admin->update();

        return $admin;
    }

    protected function validation($parameters)
    {
        return [
            "admin_id" => "required",
            'phone_number' =>  Rule::unique('admins')->ignore($parameters->get("admin_id")),
        ];
    }
}
