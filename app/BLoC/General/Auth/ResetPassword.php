<?php
namespace App\BLoC\General\Auth;

use DAI\Utils\Abstracts\Transactional;

class ResetPassword extends Transactional
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
