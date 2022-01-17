<?php

namespace App\Libraries;
use App\Jobs\ForgotPasswordEmailJob;
use Queue;

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
}