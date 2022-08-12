<?php

namespace App\BLoC\Web\AdminAuth;

use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

class UpdateAdminProfile extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        
        $admin_login = Auth::user();
        $admin = Admin::where("id",$admin_login->id)->first();
        $admin->update([
            "name" => $parameters->get('name_organizer'),
            "phone_number" => $parameters->get('phone_number'),
            "city_id" => $parameters->get('city_id')
        ]);
        return Admin::getProfile();
    }

    protected function validation($parameters)
    {
        return [
            'name_organizer' => 'required',
            'city_id' => 'required',
        ];
    }
}
