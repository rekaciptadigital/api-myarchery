<?php

namespace App\Libraries;

class Upload
{

    static $path = "";
    static $base_64 = "";
    static $file_name = "";
    static $ext = "";

    public function __construct()
    {
    }

    public static function setPath(string $path)
    {
        self::$path = $path;
        return (new self);
    }

    public static function setFileName(string $file_name)
    {
        self::$file_name = $file_name;
        return (new self);
    }

    public static function setExtention(string $ext)
    {
        self::$ext = $ext;
        return (new self);
    }

    public static function setBase64(string $base_64)
    {
        self::$base_64 = $base_64;
        return (new self);
    }

    public static function save()
    {
        $image_parts = explode(";base64,", self::$base_64);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        if (!empty(self::$ext)) {
            $image_type = self::$ext;
        }
        $image_base64 = base64_decode($image_parts[1]);
        $file = self::$path . self::$file_name . '.' . $image_type;
        $file_name2 = env('APP_HOSTNAME') . $file;

        file_put_contents($file, $image_base64);
        return $file_name2 . "#" . time();
    }


    
    public static function pdf()
    {
        $pdf_parts = explode(";base64,", self::$base_64);
        $pdf_type_aux = explode("application/", $pdf_parts[0]);
        $pdf_type = $pdf_type_aux[1];
        if (!empty(self::$ext)) {
            $pdf_type = self::$ext;
        }
        $pdf_base64 = base64_decode($pdf_parts[1]);
        $file = self::$path . self::$file_name . '.' . $pdf_type;
        $file_name2 = env('APP_HOSTNAME') . $file;

        file_put_contents($file, $pdf_base64);
        return $file_name2 . "#" . time();
    }
}
