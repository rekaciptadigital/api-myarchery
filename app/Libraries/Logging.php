<?php

namespace App\Libraries;

class Logging
{
    static $file_name = "";
    static $log_path = "";

    public function __construct()
    {
        
    }

    public static function add(array $data)
    {
        if (!file_exists(self::$log_path))
        {
            mkdir(self::$log_path, 0777, true);
        }
        $logFileData = self::$log_path .'/'. self::$file_name . '.log';
        file_put_contents($logFileData, self::toString($data) . "\n", FILE_APPEND);
    }

    public static function setFileName(string $file_name)
    {
        self::$file_name = $file_name;
        return (new self);
    }

    public static function setLogPath(string $log_path)
    {
        self::$log_path = storage_path("logs") .'/'. $log_path;
        return (new self);
    }

    private static function toString($data)
    {
        $now = date("Y-m-d H:i:s");
        $contents = "------------------------". "$now | " . $data['email'] .  "------------------------" . "\n\n" . 
                    "STATUS : ". $data['status'] . "\n" .
                    "MESSAGE: ". $data['message'] . "\n\n" .
                    "---------------------------------------------------------------------------------------". "\n";

        return $contents;
    }
}