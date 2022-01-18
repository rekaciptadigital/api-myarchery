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
        if(!$user) throw new BLoCException("Email tidak ditemukan");

        $key = "email:verify:code:" . $user->email;
        $isKeyExist = Redis::lrange($key, 0, -1);
        $isKeyExp = Redis::ttl($key);

        $code = ForgetPassword::getCode($key,$user,'user_id');  
        $send_email = ForgetPassword::setEmail($user->email)->setName($user->name)->setCode($code)->sendMail();

        return ["code" => 1, "msg" => "Kode sudah dikirim ke alamat email anda"];
    }

    protected function validation($parameters)
    {
        return [
            'email' => 'required',
        ];
    }
}
