<?php

namespace App\BLoC\App\UserAuth;

use App\Models\User;
use App\Models\UserLoginToken;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Auth;

class UserLogin extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $is_use_otp = env("REGISTER_OTP", false);
        if ($is_use_otp == true) {
            $token = Auth::guard('app-api')->setTTL(60 * 24 * 7)->attempt([
                "email" => $parameters->email,
                "password" => $parameters->password,
                "email_verified" => 1
            ]);

            $user = User::where("email", $parameters->get("email"))->first();
            if (!$user) {
                throw new BLoCException("Email anda belum terdaftar");
            }

            if ($user->email_verified != 1) {
                $otp_code = User::sendOtpAccountVerification($user->id);
                date_default_timezone_set("Asia/Jakarta");

                $expired_date = date("l-d-F-Y", $otp_code->expired_time);
                $date_format = dateFormatTranslate($expired_date);
                return [
                    "email_verified" => $user->email_verified,
                    "status" => $user->email_verified == 1 ? "Verified" : "Not Verified",
                    "time_verified" => $otp_code->expired_time,
                    "message" => "otp success dikirimkan, cek email anda dan masukkan 5 digit code verifikasi sebelum " . $date_format . " pukul " . date("H:i", $otp_code->expired_time)
                ];
            }

            if (!$token) {
                throw new BLoCException("password salah");
            }

            $user = Auth::guard('app-api')->user();
            $private_signature = Auth::payload()["jti"];

            $platform = isset($_SERVER["HTTP_X_PLATFORM"]) ? $_SERVER["HTTP_X_PLATFORM"] : "web";
            UserLoginToken::where("user_id", $user->id)->where("platform", $platform)->delete();

            $login_token = new UserLoginToken;
            $login_token->platform = $platform;
            $login_token->firebase_token = $parameters->get("firebase_token");
            $login_token->private_signature = $private_signature;
            $login_token->expired_at = date('Y-m-d H:i:s', strtotime('+' . Auth::factory()->getTTL() . ' minutes'));
            $login_token->user_id = $user->id;
            $login_token->save();
            return [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => Auth::factory()->getTTL(),
                'email_verified' => $user->email_verified,
                'status' => $user->email_verified == 1 ? "Verified" : "Not Verified",
            ];
        } else {
            $token = Auth::guard('app-api')->setTTL(60 * 24 * 7)->attempt([
                "email" => $parameters->email,
                "password" => $parameters->password,
            ]);

            if (!$token) {
                $user = User::where("email", $parameters->get("email"))->first();
                if (!$user) {
                    throw new BLoCException("Email anda belum terdaftar");
                }

                throw new BLoCException("password salah");
            }

            $user = Auth::guard('app-api')->user();
            $private_signature = Auth::payload()["jti"];

            $platform = isset($_SERVER["HTTP_X_PLATFORM"]) ? $_SERVER["HTTP_X_PLATFORM"] : "web";
            UserLoginToken::where("user_id", $user->id)->where("platform", $platform)->delete();

            $login_token = new UserLoginToken;
            $login_token->platform = $platform;
            $login_token->firebase_token = $parameters->get("firebase_token");
            $login_token->private_signature = $private_signature;
            $login_token->expired_at = date('Y-m-d H:i:s', strtotime('+' . Auth::factory()->getTTL() . ' minutes'));
            $login_token->user_id = $user->id;
            $login_token->save();
            return [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => Auth::factory()->getTTL(),
                'register_otp' => $is_use_otp
            ];
        }
    }

    protected function validation($parameters)
    {
        return [
            'email' => 'required',
            'password' => 'required',
        ];
    }
}
