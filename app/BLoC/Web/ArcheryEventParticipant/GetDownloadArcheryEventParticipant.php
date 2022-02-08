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
        $status_id =$parameters->get('status_id');
  
        if($status_id==1){
            $download_link = 'daftar_peserta_yang_sudah_bayar_'.time().'.xlsx';
        }else{
            $download_link = 'daftar_peserta_yang_belum_bayar_'.time().'.xlsx';
        }

         
        $download= Excel::store(new ArcheryEventParticipantExport($event_id,$status_id), $download_link, 'public');
       
        $destinationPath = Storage::url($download_link);
        $file_path = env('APP_URL').$destinationPath;

        return $file_path;
    
    }

    protected function validation($parameters)
    {
        return [
            'status_id' => 'required',
        ];
    }

}


