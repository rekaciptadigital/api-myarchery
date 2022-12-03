<?php

namespace App\BLoC\Web\Series;

use DAI\Utils\Abstracts\Retrieval;
use App\Exports\ArcheryEventSeriesUserPointExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class GetDownloadUserSeriePoint extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $serie_id = $parameters->get("serie_id");
        $views = new ArcheryEventSeriesUserPointExport($serie_id);
        
        $filename = '/report-serie/'.$serie_id.'/ARCHERY_USER_POINT.xlsx';
    
        $download= Excel::store($views, $filename, 'public');
        $destinationPath = Storage::url($filename);
        $file_path = env('STOREG_PUBLIC_DOMAIN').$destinationPath;

        
        return $file_path;
    
    }

    protected function validation($parameters)
    {
        return [
            'serie_id' => 'required|exists:archery_series,id',
        ];
    }

}


