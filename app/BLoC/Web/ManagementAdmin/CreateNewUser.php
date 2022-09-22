<?php

namespace App\BLoC\Web\ManagementAdmin;

use App\Jobs\CreateAccountEmailJob;
use App\Jobs\InvitationEmailJob;
use App\Models\Admin;
use App\Models\AdminRole;
use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Queue;

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
        $event_id = $parameters->get("event_id");
        $event = ArcheryEvent::find($event_id);
        $admin = Admin::where("email", $email)->first();
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

            Queue::push(new CreateAccountEmailJob([
                "email" => $admin->email,
                "name" => $admin->name,
                "password" => "12345678",
                "event_name" => $event->event_name
            ]));
        }

        $admin_role = AdminRole::where("admin_id", $admin->id)->where("event_id", $event_id)->first();
        if ($admin_role) {
            throw new BLoCException("akun ini sudah didaftarkan sebagai pengelola di event ini");
        }
        $admin_role = new AdminRole;
        $admin_role->admin_id = $admin->id;
        $admin_role->role_id = $role_id;
        $admin_role->event_id = $event_id;
        $admin_role->save();

        Queue::push(new InvitationEmailJob([
            "event_name" => $event->event_name,
            "email" => $admin->email,
            "name" => $admin->name,
        ]));
    }

    protected function validation($parameters)
    {
        return [
            "email" => "required|email",
            "name" => "required|string",
            "phone_number" => "required|string",
            "role_id" => "required|integer|exists:roles,id",
            "event_id" => "required|exists:archery_events,id"
        ];
    }
}
