<?php

namespace App\Libraries;

use App\Jobs\AccountVerificationJob;
use App\Jobs\ForgotPasswordEmailJob;
use Queue;
use Illuminate\Support\Facades\Redis;
use DAI\Utils\Exceptions\BLoCException;

class SendMail
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
        return Queue::push(new AccountVerificationJob($data));
    }

    public static function getCode($keyForADay, $keyForTenMinutes, $admin)
    {
        $code = substr(str_shuffle('1234567890'), 0, 5);
        return $code;
    }
}
