<?php

namespace App\BLoC\Web\ArcheryCategoryDetail;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;

class DeleteArcheryCategoryDetail extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $find=ArcheryEventCategoryDetail::find($parameters->get('id'));
        if($find){
            $check = ArcheryEventParticipant::where('event_category_id', $find->id)->first();
            
            if($check != null){
                throw new BLoCException("sudah ada partisipan");
            }else{
                ArcheryEventCategoryDetail::find($parameters->get('id'))->delete();
            }
        }
        
        return [];
    }

    protected function validation($parameters)
    {
        return [
            'id' => [
                'required',
                'exists:archery_event_category_details',
            ],
        ];
    }
}
