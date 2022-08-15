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
        $user->save();

        $code = substr(str_shuffle('1234567890'), 0, 5);

        $otp_code = OtpVerificationCode::where("email", $user->email)->first();
        if (!$otp_code) {
            $otp_code = new OtpVerificationCode;
        }
        $otp_code->user_id = $user->id;
        $otp_code->email = $user->email;
        $otp_code->otp_code = $code;
        $otp_code->expired_time = strtotime("+10 minutes", time());
        $otp_code->save();

        Queue::push(new AccountVerificationJob([
            "code" => $code,
            "email" => $user->email,
            "name" => $user->name,
        ]));

        return "email success dikirimkan";
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
