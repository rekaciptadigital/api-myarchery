<?php

namespace App\BLoC\App\UserAuth;

use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Validator;

class UserLogin extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $token = Auth::guard('app-api')->setTTL(60 * 24 * 7)->attempt($parameters->all());

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
            'email' => 'required|exists:users',
            'password' => 'required',
        ];
    }
}
