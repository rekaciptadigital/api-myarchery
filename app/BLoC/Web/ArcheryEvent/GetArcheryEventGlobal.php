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

        $archery_event = ArcheryEvent::select('archery_events.*', DB::raw("if(now()>archery_events.event_end_datetime,'selesai',if(now()<archery_events.event_start_datetime,'akan berlangsung',if(now()> archery_events.event_start_datetime && now()< archery_events.event_end_datetime,'sedang berlangsung','false'))) as acara "))
                         ->orderBy('archery_events.event_end_datetime', 'desc')
                         ->join('archery_event_category_details', 'archery_event_category_details.event_id', '=', 'archery_events.id')
                         ->where('status',1)
                         ->where('is_private',false)
                         ->where(function ($query) use ($event_name){
                             if(!empty($event_name)){
                                 $query->where('archery_events.event_name', 'like', '%'.$event_name.'%');
                                }
                            })
                        ->groupBy('archery_events.id');
                            
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
