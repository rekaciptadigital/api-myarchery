<?php

namespace App\BLoC\Web\ArcheryCategoryDetail;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;

class EditArcheryCategoryDetail extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        
        $admin = Auth::user();
        $datas = $parameters->get('data', []);
        foreach ($datas as $data) {
            
            $archery_category_detail = ArcheryEventCategoryDetail::find($data['id']);

            $archery_category_detail->event_id = $data['event_id'];
            $archery_category_detail->age_category_id = $data['age_category_id']; 
            $archery_category_detail->competition_category_id = $data['competition_category_id']; 
            $archery_category_detail->distance_id  = $data['distance_id']; 
            $archery_category_detail->team_category_id  = $data['team_category_id'];  
            $archery_category_detail->quota  = $data['quota']; 
            $archery_category_detail->fee = $data['fee']; 
            $archery_category_detail->save();
        }

        return $archery_category_detail;
    }

    protected function validation($parameters)
    {
        return [
            "data" => "required|array|min:1",
            "data.*.id" => "required"
        ];
    }
}
