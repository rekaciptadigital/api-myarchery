<?php

namespace App\BLoC\Web\ManagementAdmin;

use App\Models\AdminRole;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class RemoveAccessAdmin extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin_id = $parameters->get("admin_id");
        $event_id = $parameters->get("event_id");

        $admin_role = AdminRole::where("admin_id", $admin_id)->where("event_id", $event_id)->first();

        if (!$admin_role) {
            throw new BLoCException("role not found");
        }

        $admin_role->delete();

        return "success";
    }

    protected function validation($parameters)
    {
        return [
            "admin_id" => "required|integer|exists:admins,id",
            "event_id" => "required|exists:archery_events,id"
        ];
    }
}
