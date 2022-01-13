<?php

namespace App\Libraries;
use Mpdf\Output\Destination;
use Barryvdh\DomPDF\Facade as PDF;

class PdfLibrary
{
    static $final_doc = "";
    static $file_name = "";

    public function __construct()
    {
        
    }

    public static function setFinalDoc(string $final_doc)
    {
        self::$final_doc = $final_doc;
        return (new self);
    }

    public static function setFileName(string $file_name)
    {
        self::$file_name = $file_name;
        return (new self);
    }

    public static function generateIdcard()
    {
        $mpdf = new \Mpdf\Mpdf([
            'margin_left' => 0,
            'margin_right' => 0,
            'mode' => 'utf-8',
            'format' => 'A4-P',
            'orientation' => 'P',
            'bleedMargin' => 0,
            'dpi'        => 110,
            'tempDir' => public_path().'/tmp/pdf'
        ]);

        if(env("APP_ENV") != "production")
        $mpdf->SetWatermarkText('EXAMPLE');
        
        $mpdf->SetDisplayPreferences('FullScreen');
        $mpdf->WriteHTML(self::$final_doc);
        $pdf = $mpdf->Output(self::$file_name, Destination::STRING_RETURN);
        
        $base64_pdf = "data:application/pdf;base64,".base64_encode($pdf);
        return $base64_pdf;
    }
}