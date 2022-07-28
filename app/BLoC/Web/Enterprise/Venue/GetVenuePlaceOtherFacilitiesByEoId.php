<?php

namespace App\BLoC\Web\Enterprise\Venue;


use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use App\Models\VenueMasterPlaceFacility;
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
        $datas = VenueMasterPlaceFacility::where('eo_id', $admin->eo_id)->where('is_hide', false)->get();
        if (!$datas) throw new BLoCException("Data not found");

        return $datas;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
