<?php

namespace App\BLoC\Web\Enterprise\Venue\ScheduleOperational;

use App\Models\VenuePlace;
use App\Models\VenuePlaceScheduleOperational;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;

class GetVenueScheduleOperationalDetailById extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $schedule_operational = VenuePlaceScheduleOperational::find($parameters->get('id'));
        if (!$schedule_operational) throw new BLoCException("Data not found");

        $venue_place = VenuePlace::find($schedule_operational->place_id);
        if ($venue_place->eo_id != $admin->eo_id) throw new BLoCException("You're not the owner of this event");

        return $schedule_operational;
    }

    protected function validation($parameters)
    {
        return [
            "id" => "required|integer"
        ];
    }

}
