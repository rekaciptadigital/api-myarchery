<?php

namespace App\BLoC\Web\ArcheryEvent;

use App\Models\ArcheryEvent;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Abstracts\Retrieval;

class FindArcheryEvent extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        
        $admin = Auth::user();
        $archery_events= ArcheryEvent::getCategories($parameters->get('id'));
        $output= [];
       
        foreach ($archery_events as $key => $value ){
            $output[]=[
                'event_category_details_id' => $value['key'],
                'age_category' => $value['label_age'],
                'competition_category' => $value['label_competition_categories'],
                'distances_category' => $value['label_distances'],
                'team_category' => $value['label_team_categories'],
                'type' => $value['type'],
            ];
        }
        
        return $output;
       
    }

    protected function validation($archery_event)
    {
        return [
            'id' => 'required|exists:archery_events,id',
        ];
    }
}