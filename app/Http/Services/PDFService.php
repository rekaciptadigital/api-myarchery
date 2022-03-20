<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;

class PDFService
{
    public function generate($html, $filePath, $fileName)
    {
       $snappy = \App::make('snappy.pdf');
        $options = [
            'margin-top'    => 10,
            'margin-bottom' => 15,
            'page-size'     => 'a4',
        ];
        $generate = $snappy->generateFromHtml($html, ''.$filePath.'/'.$fileName.'', $options);
        return true;
    }
}