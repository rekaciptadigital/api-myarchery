<?php
namespace App\BLoC\Web\AdminAuth;

use DAI\Utils\Abstracts\Transactional;
use App\Models\Admin;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Redis;


class ResetPassword extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $email=$parameters->get('email');
        $code=$parameters->get('code');
        $password=$parameters->get('password');
        $retype_password=$parameters->get('retype_password');
        $keyForTenMinutes = "email:verify:code:10minutes:" . $email;

        $admin = Admin::where('email', $email)->first();
        if(!$admin) throw new BLoCException("Email tidak ditemukan");

        $checkKey = Redis::lrange($keyForTenMinutes, 0, -1);
        $ExpKey = Redis::ttl($keyForTenMinutes);
        
        if($ExpKey>=0){
            if($checkKey[0] != $code){
                throw new BLoCException("Kode tidak sesuai");
            }else{
                throw new BLoCException("Kode sesuai");
            }
        }else{
            throw new BLoCException("Kode sudah expire");
        }
        
        
    }

    protected function validation($parameters)
    {
        return [
            'code' => 'required',
            'email' => 'required',
            'password' => 'required',
            'retype_password' => 'required',
        ];
    }
}
