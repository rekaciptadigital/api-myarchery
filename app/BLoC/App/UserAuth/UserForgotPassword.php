<?php

namespace App\BLoC\App\UserAuth;

use DAI\Utils\Abstracts\Transactional;

class UserForgotPassword extends Transactional
{
    public function getDescription()
    {
        return "Forgot Password";
    }

    protected function process($parameters)
    {
        return $parameters;
    }

    protected function validation($parameters)
    {
        return [
            'email' => 'required',
        ];
    }
}
