<?php

namespace App\BLoC\Web\ArcheryEventQualificationTime;

use App\Models\ArcheryEventQualificationTime;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Abstracts\Retrieval;

class GetArcheryEventQualificationTime extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $event_id=$parameters->get('event_id');
        $category_detail_id=$parameters->get('category_detail_id');
        
        $archery_category_detail = ArcheryEventQualificationTime::getQualificationById($event_id,$category_detail_id);       

        $output= [];
        
        foreach ($archery_category_detail as $key => $value ){
            $output[]= [
                'event_id' => $value['event_id'],
                'id_qualification_time' => $value['id'],
                'category_detail_id' => $value['category_detail_id'],
                'event_start_datetime' => $value['event_start_datetime'],
                'event_end_datetime' => $value['event_end_datetime'],
            ];
        }
        
        return $output;

        
    }
    protected function validation($parameters)
    {
        return [
            "event_id" => "required",
        ];
    }
}
