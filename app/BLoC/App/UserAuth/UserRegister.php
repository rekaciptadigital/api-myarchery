<?php

namespace App\BLoC\App\UserAuth;

use App\Jobs\AccountVerificationJob;
use App\Models\OtpVerificationCode;
use App\Models\User;
use App\Models\UserNotifTopic;
use Queue;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserRegister extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $is_use_otp_register = env("REGISTER_OTP", false);
        if ($is_use_otp_register == true) {
            $user = User::where("email", $parameters->get('email'))->first();
            if ($user && $user->email_verified == 1) {
                throw new BLoCException("email anda sudah terdaftar");
            }

            if (!$user) {
                $user = new User;
            }

            $user->name = $parameters->get('name');
            $user->email = $parameters->get('email');
            $user->password = Hash::make($parameters->get('password'));
            $user->date_of_birth = $parameters->get('date_of_birth');
            $user->gender = $parameters->get('gender');
            $user->phone_number = $parameters->get('phone_number');
            $user->email_verified = 0;
            $user->verify_status = 5;
            $user->save();

            $otp_code = User::sendOtpAccountVerification($user->id);

            date_default_timezone_set("Asia/Jakarta");

            $expired_date = date("l-d-F-Y", $otp_code->expired_time);
            $date_format = dateFormatTranslate($expired_date);
            return [
                'REGISTER_OTP' => $is_use_otp_register,
                "email_verified" => $user->email_verified,
                "status" => $user->email_verified == 1 ? "Verified" : "Not Verified",
                "time_verified" => $otp_code->expired_time,
                "message" => "otp success dikirimkan, cek email anda dan masukkan 5 digit code verifikasi sebelum " . $date_format . " pukul " . date("H:i", $otp_code->expired_time)
            ];
        } else {
            $user_check = User::where("email", $parameters->get('email'))->first();
            if ($user_check) {
                throw new BLoCException("email anda sudah terdaftar");
            }

            $user = new User;
            $user->name =  $parameters->get('name');
            $user->email = $parameters->get('email');
            $user->password = Hash::make($parameters->get('password'));
            $user->date_of_birth = $parameters->get('date_of_birth');
            $user->gender = $parameters->get('gender');
            $user->phone_number = $parameters->get('phone_number');
            $user->verify_status = 5;
            $user->email_verified = 0;
            $user->save();

            $token = Auth::guard('app-api')->setTTL(60 * 24 * 7)->attempt([
                'email' => $parameters->get('email'),
                'password' => $parameters->get('password'),
            ]);

            UserNotifTopic::saveTopic("USER_" . $user->id, $user->id);
            return [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => Auth::factory()->getTTL(),
                'register_otp' => $is_use_otp_register
            ];
        }
    }

    protected function validation($parameters)
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6|confirmed',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
        ];
    }
}
