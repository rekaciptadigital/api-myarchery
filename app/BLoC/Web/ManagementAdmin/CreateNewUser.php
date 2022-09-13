<?php

namespace App\BLoC\Web\ManagementAdmin;

use App\Models\Admin;
use App\Models\AdminRole;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateNewUser extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $email = $parameters->get("email");
        $name = $parameters->get("name");
        $phone_number = $parameters->get("phone_number");
        $role_id = $parameters->get("role_id");
        $admin = Admin::where("email", $email)->firts();
        if (!$admin) {
            Validator::make($parameters->all(), [
                "email" => "unique:admins,email",
                "phone_number" => "unique:admins,phone_number"
            ]);

            $admin = new Admin;
            $admin->name = $name;
            $admin->email = $email;
            $password = Hash::make("12345678");
            $admin->password = $password;
            $admin->phone_number = $phone_number;
            $admin->save();
        }
        
        $admin_role = AdminRole::where("admin_id", $admin->id)->first();
        if (!$admin_role) {
            $admin_role = new AdminRole;
        }

        $admin_role->admin_id = $admin->id;
        $admin_role->role_id = $role_id;
        $admin_role->save();
    }

    protected function validation($parameters)
    {
        return [
            "email" => "required|email",
            "name" => "required|string",
            "phone_number" => "required|string",
            "role_id" => "required|integer|exists:roles,id"
        ];
    }
}
