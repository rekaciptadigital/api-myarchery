<?php

namespace App\BLoC\Web\ManagementAdmin;

use App\Models\Admin;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class GetDetailAdmin extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin_id = $parameters->get("admin_id");
        $event_id = $parameters->get("event_id");

        $admin = Admin::select("admins.id", "admins.name", "admins.email", "admins.phone_number", "admin_roles.role_id", "roles.display_name as role_name")->join("admin_roles", "admin_roles.admin_id", "=", "admins.id")
            ->join("roles", "roles.id", "=", "admin_roles.role_id")
            ->where("admin_roles.event_id", $event_id)
            ->where("admins.id", $admin_id)
            ->first();
        if (!$admin) {
            throw new BLoCException("admin tidak ditemukan");
        }
        return $admin;
    }

    protected function validation($parameters)
    {
        return [
            "admin_id" => "required|integer|exists:admins,id",
            "event_id" => "required|exists:archery_events,id"
        ];
    }
}
