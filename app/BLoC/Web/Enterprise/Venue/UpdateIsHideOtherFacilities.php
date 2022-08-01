<?php

namespace App\BLoC\Web\Enterprise\Venue;


use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use App\Models\VenueMasterPlaceFacility;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;

class UpdateIsHideOtherFacilities extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $place_facilities = VenueMasterPlaceFacility::find($parameters->get('id'));
        if (!$place_facilities) throw new BLoCException("Data not found");
        if ($place_facilities->eo_id != $admin->eo_id) throw new BLoCException("You're not the owner of this event");
        
        $place_facilities->update([
            "is_hide" => $parameters->get('is_hide')
        ]);

        return $place_facilities;
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|integer',
        ];
    }
}
