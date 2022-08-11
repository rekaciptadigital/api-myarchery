<?php

namespace App\BLoC\Web\Enterprise\Venue\Products\Session;

use App\Models\VenuePlace;
use App\Models\VenuePlaceProductSession;
use App\Models\VenuePlaceScheduleOperational;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;

class GetVenueProductSessionDetailById extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $product_session = VenuePlaceProductSession::find($parameters->get('id'));
        if (!$product_session) throw new BLoCException("Data not found");

        $schedule_operational = VenuePlaceScheduleOperational::find($product_session->schedule_operational_id);
        $venue_place = VenuePlace::find($schedule_operational->place_id);
        if ($venue_place->eo_id != $admin->eo_id) throw new BLoCException("You're not the owner of this event");

        return $product_session;
    }

    protected function validation($parameters)
    {
        return [
            "id" => "required|integer"
        ];
    }

}
