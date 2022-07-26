<?php
namespace App\BLoC\Web\AdminAuth;

use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Helpers\BLoC;
use App\Models\Admin;

class CheckAdminRegister extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Admin::where('email', $parameters->get('email'))->first();
        return [
            "is_registered" => $admin ? true : false
        ];
    }

    protected function validation($parameters)
    {
        return [
            'email' => 'required',
        ];
    }
}