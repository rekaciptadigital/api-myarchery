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

class ValidateAccoutVerification extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $email = $parameters->get("email");
        $code = $parameters->get("code");
        $otp_code = OtpVerificationCode::where("email", $email)
            ->where("otp_code", $code)
            ->first();
        if (!$otp_code) {
            throw new BLoCException("code not found");
        }

        if ($otp_code->expired_time < time()) {
            // throw new BLoCException("code expired");
        }

        $user = User::find($otp_code->user_id);
        if (!$user) {
            throw new BLoCException("user not found");
        }

        $user->email_verified = 1;
        $user->save();


        // $token = Auth::guard('app-api')->setTTL(60 * 24 * 7)->login($user);
        $token = Auth::guard('app-api')->setTTL(60 * 24 * 7)->attempt(["email" => $parameters->email, "password" => "12345678"]);

        // $token = ($user_login = Auth::getProvider()->retrieveByCredentials(["email" => $email]))
        //     ? Auth::login($user_login)
        //     : false;

        UserNotifTopic::saveTopic("USER_" . $user->id, $user->id);
        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::factory()->getTTL()
        ];
    }

    protected function validation($parameters)
    {
        return [
            'code' => 'required',
            'email' => 'required|string',
        ];
    }
}
