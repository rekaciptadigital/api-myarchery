<?php

namespace App\BLoC\App\UserAuth;

use App\Models\User;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;

class ResendOtpAccountVerificationCode extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $email = $parameters->get("email");
        $user = User::where("email", $email)->where("email_verified", 0)->first();
        if (!$user) {
            throw new BLoCException("user not found");
        }

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

    protected function validation($parameters)
    {
        return [
            'email' => "required|email:rfc,dns",
        ];
    }
}
