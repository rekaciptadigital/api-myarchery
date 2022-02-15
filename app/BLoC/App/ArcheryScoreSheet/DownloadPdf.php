<?php

namespace App\BLoC\App\ArcheryScoreSheet;

use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Helpers\BLoCParams;
use Illuminate\Support\Facades\Auth;
use Mpdf\Output\Destination;

class DownloadPdf extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_name = "The HuB Scoring 2021";
        $name = "Aditya Priyantoro";
        $category = "Individu-Umum-Barebow-50m";
        $code = "1-15";
        $date = "2021-09-28";

        $data  = [
            'event_name' => $event_name,
            'name' => $name,
            'category' => $category,
            'code' => $code,
            'date' => $date
        ];

        $mpdf = new \Mpdf\Mpdf([
            'margin_left' => 0,
            'margin_right' => 0,
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'orientation' => 'L',
            'bleedMargin' => 0,
            'dpi'        => 110,
            'tempDir' => public_path() . '/tmp/pdf'
        ]);

        $html = \view('template.ScoreSheet', $data);

        $mpdf->WriteHTML($html);
        $mpdf->Output('coba.pdf');
    }

    protected function validation($parameters)
    {
        return [];
    }
}
