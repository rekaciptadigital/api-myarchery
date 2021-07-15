<?php

namespace App\BLoC\General;

use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;

class Login extends Transactional
{
    public function getDescription()
    {
    }

    protected function prepare($params, $original_params)
    {
        return $params;
    }

    protected function process($params, $original_params)
    {
        $token = Auth::setTTL(60 * 24 * 7)->attempt($params);

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::factory()->getTTL()
        ];
    }

    protected function rules()
    {
        return [
            'email' => 'required',
            'password' => 'required',
        ];
    }
}
