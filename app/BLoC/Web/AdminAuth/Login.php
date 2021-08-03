<?php

namespace App\BLoC\Web\AdminAuth;

use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class Login extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $token = Auth::setTTL(60 * 24 * 7)->attempt($parameters->all());
        if (!$token) {
            throw new BLoCException(__('response.invalid_credential'));
        }
        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::factory()->getTTL()
        ];
    }

    protected function validation($parameters)
    {
        return [
            'email' => 'required',
            'password' => 'required',
        ];
    }
}
