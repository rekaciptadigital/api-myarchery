<?php

namespace App\BLoC\Web\AdminAuth;

use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\Libraries\ForgetPassword;
use App\Models\Admin;

class ForgotPassword extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Admin::where('email', $parameters->get('email'))->first();
        if(!$admin) throw new BLoCException("Email tidak ditemukan");

        $key = "email:verify:code:" . $admin->email;
        $isKeyExist =  Redis::lrange($key, 0, -1);
        $isKeyExp = Redis::ttl($key);

        if($isKeyExist) {
            $maxCount= count($isKeyExist);
            //max try per day 3 times
            if($maxCount>=3){
                //minus means expired
                if($isKeyExp<=0){
                    Redis::del($key);
                }
                throw new BLoCException("Anda sudah mencoba forgot password 3x hari ini, coba lagi di jam berikutnya");
            }else{
                $code=$this->pushKey($admin,$key);
            }
        } else {
            $code=$this->pushKey($admin,$key);
        }
        
        $send_email = ForgetPassword::setEmail($admin->email)->setName($admin->name)->setCode($code)->sendMail();
        return ["code" => 1, "msg" => "Kode sudah dikirim ke alamat email anda"];
    }

    protected function validation($parameters)
    {
        return [
            'email' => 'required',
        ];
    }

}
