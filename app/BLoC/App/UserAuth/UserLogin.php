<?php

namespace App\BLoC\App\UserAuth;

use App\Models\User;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class UserLogin extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $token = Auth::guard('app-api')->setTTL(60 * 24 * 7)->attempt($parameters->all());
        $error_message = "Password salah";
        if (!$token) {
            $user = User::where("email", $parameters->get("email"))->first();
            if (!$user) {
                $error_message = "Email anda belum terdaftar";
            }
            throw new BLoCException($error_message);
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
