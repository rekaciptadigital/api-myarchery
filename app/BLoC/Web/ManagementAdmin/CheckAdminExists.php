<?php

namespace App\BLoC\Web\ManagementAdmin;

use App\Models\Admin;
use App\Models\AdminRole;
use App\Models\Role;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

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
            $admin_role = AdminRole::where("admin_id", $admin->id)->where("event_id", $event_id)->first();
            if ($admin_role) {
                throw new BLoCException("email ini sudah terdaftar");
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
