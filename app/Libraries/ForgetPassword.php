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

    public static function getCode($key,$admin,$value_id)
    {
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
                $code=self::pushKey($admin,$key,$value_id);
            }
        } else {
            $code=self::pushKey($admin,$key,$value_id);
        }

        return $code;
    }

    public static function pushKey($admin,$key,$value_id)
    {
            $code = substr(str_shuffle('1234567890'),0,5);
            $value = [$value_id => $admin->id, "code" => $code];
            $set = Redis::rpush($key, json_encode($value));
            $set = Redis::expire($key, 3600);

            return $code;
    }

}