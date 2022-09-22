<?php

namespace App\BLoC\Web\ManagementAdmin;

use App\Models\Admin;
use App\Models\AdminRole;
use App\Models\ArcheryEvent;
use App\Models\Role;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetListAdmin extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();

        $event_id = $parameters->get("event_id");
        $event = ArcheryEvent::where("id", $event_id)->where("admin_id", $admin->id)->first();
        if (!$event) {
            throw new BLoCException("event not found");
        }

        $list_admin = Admin::select("admins.id", "admins.name", "admins.email", "admins.phone_number", "admin_roles.role_id", "roles.display_name as role_name")->join("admin_roles", "admin_roles.admin_id", "=", "admins.id")
            ->join("roles", "roles.id", "=", "admin_roles.role_id")
            ->where("admin_roles.event_id", $event_id)
            ->get();

        return $list_admin;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|exists:archery_events,id"
        ];
    }
}
