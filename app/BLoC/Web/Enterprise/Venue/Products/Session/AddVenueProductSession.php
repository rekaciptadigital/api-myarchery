<?php

namespace App\BLoC\Web\Enterprise\Venue\Products\Session;

use App\Models\VenuePlace;
use App\Models\VenuePlaceProductSession;
use App\Models\VenuePlaceScheduleOperational;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;

class AddVenueProductSession extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();

        $schedule_operational = VenuePlaceScheduleOperational::find($parameters->get('schedule_operational_id'));
        if (!$schedule_operational) throw new BLoCException("Data not found");
        if ($schedule_operational->is_open == false) throw new BLoCException("Sorry, you're choose the closed day. Please select another day");

        $venue_place = VenuePlace::find($schedule_operational->place_id);
        if ($venue_place->eo_id != $admin->eo_id) throw new BLoCException("You're not the owner of this event");

        $product_session = new VenuePlaceProductSession();
        $product_session->schedule_operational_id = $parameters->get('schedule_operational_id');
        $product_session->start_time = $parameters->get('start_time');
        $product_session->end_time = $parameters->get('end_time');
        $product_session->total_budrest = $parameters->get('total_budrest');
        $product_session->total_target = $parameters->get('total_target');
        $product_session->max_capacity = $parameters->get('max_capacity');

        $product_session->save();

        return $product_session;
    }

    protected function validation($parameters)
    {
        return [
            "schedule_operational_id" => "required|integer",
        ];
    }

}
