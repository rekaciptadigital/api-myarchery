<?php

namespace App\BLoC\Web\Enterprise\Venue;


use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use App\Models\VenueMasterPlaceFacility;
use Illuminate\Support\Facades\Auth;

class GetVenueMasterPlaceFacilities extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
       
        $place_facilities = VenueMasterPlaceFacility::where("eo_id","0")->get();
    
        return $place_facilities;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
