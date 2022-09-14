<?php

namespace App\BLoC\Web\ManagementAdmin;

use App\Models\Admin;
use App\Models\AdminRole;
use App\Models\Role;
use DAI\Utils\Abstracts\Retrieval;

class CheckAdminExists extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $email = $parameters->get("email");
        $event_id = $parameters->get("event_id");

        $admin = Admin::where("email", $email)->first();
        if ($admin) {
            $role_detail = null;
            $admin_role = AdminRole::where("admin_id", $admin->id)->where("event_id", $event_id)->first();
            if ($admin_role) {
                $role = Role::find($admin_role->role_id);
                if ($role) {
                    $role_detail = $role;
                    $admin->role_detail = $role_detail;
                }
            }
        }
        return $admin;
    }

    protected function validation($parameters)
    {
        return [
            "email" => "required|email",
            "event_id" => "required|exists:archery_events,id"
        ];
    }
}
