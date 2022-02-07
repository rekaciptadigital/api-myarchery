<?php

namespace App\Exports;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEvent;
use Maatwebsite\Excel\Concerns\FromCollection;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ArcheryEventParticipantExport implements FromView
{
    protected $event_id;

    function __construct($event_id) {
            $this->event_id = $event_id;
    }

    public function view(): View
    {
        $event_name= ArcheryEvent::where('id',$this->event_id)->first();
        if (!$event_name){
            throw new BLoCException("event id tidak ditemukan");
        }
        
        $data= ArcheryEventParticipant::where('status',1)->where('event_id',$this->event_id)->get();
        if ($data->isEmpty()){
            throw new BLoCException("tidak ada partisipan pada event tersebut");
        }
        
                
        return view('reports.participant_event', [
            'datas' => $data,
            'event_name'=> strtoupper($event_name['event_name'])

        ]);
    }
}


