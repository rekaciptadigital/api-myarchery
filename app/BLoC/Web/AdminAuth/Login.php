<?php

namespace App\BLoC\Web\AdminAuth;

use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\AdminLoginToken;

class Login extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $token = Auth::setTTL(60 * 24 * 7)->attempt(["email" => $parameters->get("email"), "password" => $parameters->get("password")]);
        $error_message = "Password salah";
        if (!$token) {
            $admin = Admin::where("email", $parameters->get("email"))->first();
            if (!$admin) {
                $error_message = "Email anda belum terdaftar";
            }
            throw new BLoCException($error_message);
        }

        $admin = Auth::user();
        $private_signature = Auth::payload()["jti"];

        $platform = isset($_SERVER["HTTP_X_PLATFORM"]) ? $_SERVER["HTTP_X_PLATFORM"] : "web";
        // AdminLoginToken::where("admin_id",$admin->id)->where("platform",$platform)->delete();

        $login_token = new AdminLoginToken;
        $login_token->platform = $platform;
        $login_token->firebase_token = $parameters->get("firebase_token");
        $login_token->private_signature = $private_signature;
        $login_token->expired_at = date('Y-m-d H:i:s', strtotime('+' . Auth::factory()->getTTL() . ' minutes'));
        $login_token->admin_id = $admin->id;
        $login_token->save();

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
