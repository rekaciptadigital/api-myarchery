<?php

namespace App\BLoC\App\UserAuth;

use App\Models\User;
use App\Models\UserLoginToken;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class ResendOtpAccountVerificationCode extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $email = $parameters->get("email");
        User::where
    }

    protected function validation($parameters)
    {
        return [
            'email' => 'required',
        ];
    }
}
