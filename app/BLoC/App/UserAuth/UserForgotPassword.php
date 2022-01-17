<?php

namespace App\BLoC\App\UserAuth;

use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\Libraries\ForgetPassword;
use App\Models\User;

class UserForgotPassword extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = User::where('email', $parameters->get('email'))->first();
        if(!$user) throw new BLoCException("Email not found");

        $key = "email:verify:code:" . $user->email;
        $isKeyExist = Redis::get($key);
        if($isKeyExist) {
            $value = json_decode($isKeyExist, true);
            $code = $value['code'];
        } else {
            $code = substr(str_shuffle('1234567890'),0,5);
            $value = ["user_id" => $user->id, "code" => $code];
            $set = Redis::set($key, json_encode($value), 'EX', 3600);
        }
        
        $send_email = ForgetPassword::setEmail($user->email)->setName($user->name)->setCode($code)->sendMail();
        return true;
    }

    protected function validation($parameters)
    {
        return [
            'email' => 'required',
        ];
    }
}
