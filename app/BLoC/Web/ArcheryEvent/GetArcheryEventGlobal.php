<?php

namespace App\BLoC\Web\ArcheryEvent;

use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;
use App\Models\ArcheryEventParticipant;
use Illuminate\Support\Facades\DB;

class GetArcheryEventGlobal extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $limit = !empty($parameters->get('limit')) ? $parameters->get('limit') : 1;
        $archery_event = ArcheryEvent::select('*',
                        DB::raw("if(now()>event_end_datetime,'selesai',if(now()<event_start_datetime,'akan berlangsung',if(now()> event_start_datetime && now()< event_end_datetime,'sedang berlangsung','false'))) as acara "))
                        ->orderBy('event_end_datetime', 'desc')
                        ->limit($limit)
                        ->get();

        
        return $archery_event;
    }

    protected function validation($parameters)
    {
        return [
            'limit' => 'required'
        ];
    }
}
