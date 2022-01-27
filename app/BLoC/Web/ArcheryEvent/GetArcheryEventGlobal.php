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
        $page = $parameters->get('page');
        $offset = ($page - 1) * $limit;
        $event_name = $parameters->get('event_name');

        $archery_event = ArcheryEvent::select('*', DB::raw("if(now()>event_end_datetime,'selesai',if(now()<event_start_datetime,'akan berlangsung',if(now()> event_start_datetime && now()< event_end_datetime,'sedang berlangsung','false'))) as acara "))
                         ->orderBy('event_end_datetime', 'desc')
                         ->where('status',1)
                         ->where(function ($query) use ($event_name){
                            if(!empty($event_name)){
                                $query->where('archery_events.event_name', 'like', '%'.$event_name.'%');
                            }
                         });

        $total_page = ceil($archery_event->count() / $limit);
        $data = $archery_event->limit($limit)->offset($offset)->get();

        if(!empty($page)){
            $results = array(
                'data' => $data,
                'total_page' => $total_page,
            );
        } else {
            $results = $data;
        }

        return $results;
    }

    protected function validation($parameters)
    {
        return [
            'limit' => 'required'
        ];
    }
}
