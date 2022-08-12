<?php

namespace App\BLoC\Web\Enterprise\Venue\Products;

use App\Models\VenuePlace;
use App\Models\VenuePlaceScheduleOperationalSession;
use App\Models\VenuePlaceScheduleOperational;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;

class GetAllProductVenuePlace extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();

        $venue_place = VenuePlace::find($parameters->get('place_id'));
        if ($venue_place->eo_id != $admin->eo_id) throw new BLoCException("You're not the owner of this venue");
        
        $result['session'] = VenuePlaceScheduleOperationalSession::getListScheduleOperationalSessionByPlaceId($parameters->get('place_id'));
        $result['product'] = null;

        return $result;
    }

    protected function validation($parameters)
    {
        return [
            "place_id" => "required|integer"
        ];
    }

}