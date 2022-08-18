<?php

namespace App\BLoC\Web\Enterprise\Venue\ScheduleHoliday;

use App\Models\VenuePlace;
use App\Models\VenuePlaceScheduleHoliday;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;

class GetListVenueScheduleHolidayByPlaceId extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();

        $venue_place = VenuePlace::find($parameters->get('place_id'));
        if ($venue_place->eo_id != $admin->eo_id) throw new BLoCException("You're not the owner of this event");

        $schedule_holidays = VenuePlaceScheduleHoliday::where('place_id', $parameters->get('place_id'))->get();
        if (!$schedule_holidays) throw new BLoCException("Data not found");

        return $schedule_holidays;
    }

    protected function validation($parameters)
    {
        return [
            "place_id" => "required|integer"
        ];
    }

}