<?php

namespace App\BLoC\Web\Enterprise\Venue;


use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use App\Models\VenueMasterPlaceFacilities;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;

class GetVenuePlaceOtherFacilitiesByEoId extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $datas = VenueMasterPlaceFacilities::where('eo_id', $admin->eo_id)->get();
        if (!$datas) throw new BLoCException("Data not found");

        return $datas;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
