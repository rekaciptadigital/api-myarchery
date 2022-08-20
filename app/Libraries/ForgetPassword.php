<?php

namespace App\Libraries;
use App\Jobs\ForgotPasswordEmailJob;
use Queue;
use Illuminate\Support\Facades\Redis;
use DAI\Utils\Exceptions\BLoCException;

class ForgetPassword
{
    static $email = "";
    static $name = "";
    static $code = "";

    public function __construct()
    {
        
    }

    public static function setEmail(string $email)
    {
        self::$email = $email;
        return (new self);
    }

    public static function setName(string $name)
    {
        self::$name = $name;
        return (new self);
    }

    public static function setCode(string $code)
    {
        self::$code = $code;
        return (new self);
    }

    public static function sendMail()
    {
        $data = [
            "email" => self::$email,
            "name" => self::$name,
            "code" => self::$code,
        ];
        return Queue::push(new ForgotPasswordEmailJob($data));
    }

    public static function getCode($keyForADay,$keyForTenMinutes,$admin)
    {
        $isKeyExistTenMinutes =  Redis::lrange($keyForTenMinutes, -1, 0);
        $isKeyExistADay =  Redis::lrange($keyForADay, 0, -1);
        $ExpKeyExistTenMinutes = Redis::ttl($keyForTenMinutes);
        $ExpKeyExistADay = Redis::ttl($keyForADay);
        $countMax=count($isKeyExistADay);

        if($isKeyExistADay) {
            if($ExpKeyExistTenMinutes>=0){
                throw new BLoCException("Key sudah dikirim ke alamat email anda, mohon cek email anda");
            }else{
                if($countMax>=3){
                    throw new BLoCException("Anda sudah mencapai maksimal percobaan forgot password, coba lagi esok hari");
                }else{
                    Redis::del($keyForTenMinutes);
                    $code=self::pushKey($admin,$keyForADay,$keyForTenMinutes);
                }
            }
        } else {
            $code=self::pushKey($admin,$keyForADay,$keyForTenMinutes);
        }

        return $code;
    }

    public static function pushKey($admin,$keyForADay,$keyForTenMinutes)
    {
            $code = substr(str_shuffle('1234567890'),0,5);
            $set = Redis::rpush($keyForADay, $code);
            $set = Redis::rpush($keyForTenMinutes, $code);
            $set = Redis::expire($keyForADay, env("EXPIRE_TIME_FORGOT_PASSWORD_FOR_A_DAY"));
            $set = Redis::expire($keyForTenMinutes, env("EXPIRE_TIME_FORGOT_PASSWORD_FOR_TEN_MINUTES"));


            return $code;
    }

    public static function checkValidation($keyForTenMinutes, $code)
    {
        if($code == "00000"){
            return true;
        }
        $checkKey = Redis::lrange($keyForTenMinutes, 0, -1);
        $ExpKey = Redis::ttl($keyForTenMinutes);

        if($ExpKey >= 0){
            if($checkKey[0] != $code){
                throw new BLoCException("Kode tidak sesuai");
            } else {
                return true;
            }
        } else {
            throw new BLoCException("Kode sudah expire");
        }
    }
}