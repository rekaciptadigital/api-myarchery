<?php

namespace App\BLoC\Web\ManagementAdmin;

use App\Models\Admin;
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

        $admin = Admin::where("email", $email)->first();
        return $admin;
    }

    protected function validation($parameters)
    {
        return [
            "email" => "required|email",
        ];
    }
}
