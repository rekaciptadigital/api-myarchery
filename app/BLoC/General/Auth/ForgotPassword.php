<?php

namespace App\BLoC\General\Auth;

use DAI\Utils\Abstracts\Transactional;

class ForgotPassword extends Transactional
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
