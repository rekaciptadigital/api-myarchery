<?php

namespace App\BLoC\Web\Enterprise\Venue;


use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use App\Models\VenueMasterPlaceCapacityArea;
use Illuminate\Support\Facades\Auth;

class GetVenueMasterPlaceCapacityArea extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
       
        $capacity_area = VenueMasterPlaceCapacityArea::where("eo_id","0")->get();
    
        return $capacity_area;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
