<?php
namespace App\BLoC\App\UserAuth;

use DAI\Utils\Abstracts\Transactional;

class UserResetPassword extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        return $parameters;
    }

    protected function validation($parameters)
    {
        return [
            'token' => 'required',
            'password' => 'required',
        ];
    }
}
