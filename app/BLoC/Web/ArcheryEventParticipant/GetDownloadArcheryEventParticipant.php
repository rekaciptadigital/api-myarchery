<?php

namespace App\BLoC\Web\ArcheryEventParticipant;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEvent;
use App\Models\ArcheryQualificationSchedules;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventS;
use DAI\Utils\Abstracts\Retrieval;
use App\Exports\ArcheryEventParticipantExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

class GetDownloadArcheryEventParticipant extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get('event_id');
        
        $filename = '/report-event/'.$event_id.'/ARCHERY_EVENT_PARTISIPANT.xlsx';
    
        $download= Excel::store(new ArcheryEventParticipantExport($event_id), $filename, 'public');
       
        $destinationPath = Storage::url($filename);
        $file_path = env('STOREG_PUBLIC_DOMAIN').$destinationPath;
        return $file_path;
    
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required',
        ];
    }

}


