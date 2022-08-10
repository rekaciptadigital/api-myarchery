<?php

namespace App\BLoC\Web\Enterprise\Venue\ScheduleHoliday;

use App\Models\VenuePlace;
use App\Models\VenuePlaceScheduleHoliday;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;

class DeleteVenueScheduleHoliday extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $schedule_holiday = VenuePlaceScheduleHoliday::find($parameters->get('id'));
        if (!$schedule_holiday) throw new BLoCException("Data not found");

        $venue_place = VenuePlace::find($schedule_holiday->place_id);
        if ($venue_place->eo_id != $admin->eo_id) throw new BLoCException("You're not the owner of this event");

        $schedule_holiday->delete();

        return "success";
        
    }

    protected function validation($parameters)
    {
        return [
            "id" => "required|integer"
        ];
    }

}