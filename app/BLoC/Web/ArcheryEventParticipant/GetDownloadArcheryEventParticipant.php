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
        
        $filename = 'ARCHERY_EVENT_PARTISIPANT_'.time().'.xlsx';
    
        $download= Excel::store(new ArcheryEventParticipantExport($event_id), $filename, 'public');
       
        $destinationPath = Storage::url($filename);
        $file_path = env('APP_HOSTNAME').$destinationPath;
        return $file_path;
    
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required',
        ];
    }

}


