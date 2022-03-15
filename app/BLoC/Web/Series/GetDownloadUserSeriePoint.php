<?php

namespace App\BLoC\Web\Series;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEvent;
use App\Models\ArcheryQualificationSchedules;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventS;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventSerie;
use App\Models\ArcherySeriesUserPoint;
use App\Models\ArcheryEventElimination;
use App\Models\ArcherySeriesCategory;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventEliminationMember;
use App\Models\ArcheryEventParticipantNumber;
use DAI\Utils\Abstracts\Retrieval;
use App\Exports\ArcheryEventSeriesUserPointExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;

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
            'serie_id' => 'required',
        ];
    }

}


