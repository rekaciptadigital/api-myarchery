<?php

namespace App\BLoC\Web\Enterprise\Venue\ScheduleHoliday;

use App\Models\VenuePlace;
use App\Models\VenuePlaceScheduleHoliday;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;

class AddVenueScheduleHoliday extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $venue_place = VenuePlace::find($parameters->get('place_id'));
        if (!$venue_place) throw new BLoCException("Data not found");
        if ($venue_place->eo_id != $admin->eo_id) throw new BLoCException("You're not the owner of this event");

        $schedule_holiday = new VenuePlaceScheduleHoliday();
        $schedule_holiday->place_id = $parameters->get('place_id');
        $schedule_holiday->start_at = $parameters->get('start_at');
        $schedule_holiday->end_at = $parameters->get('end_at');
        $schedule_holiday->save();

        return $schedule_holiday;
    }

    protected function validation($parameters)
    {
        return [
            "place_id" => "required|integer",
            "start_at" => "required",
            "end_at" => "required",
        ];
    }

}