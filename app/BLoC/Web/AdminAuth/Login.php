<?php

namespace App\BLoC\Web\AdminAuth;

use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

class Login extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $token = Auth::setTTL(60 * 24 * 7)->attempt($parameters->all());
        $error_message = "Password salah";
        if (!$token) {
            $admin = Admin::where("email", $parameters->get("email"))->first();
            if (!$admin) {
                $error_message = "Email anda belum terdaftar";
            }
            throw new BLoCException($error_message);
        }

        $admin = Auth::user();

        return [
            'profile' => $admin,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Admin::getProfile()
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
