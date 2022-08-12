<?php

namespace App\BLoC\Web\Enterprise\Venue\Products\SessionSetting;

use App\Models\VenuePlace;
use App\Models\VenuePlaceScheduleOperationalSession;
use App\Models\VenuePlaceScheduleOperational;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;

class AddVenueSessionSetting extends Transactional
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

        $session_setting = new VenuePlaceScheduleOperationalSession();
        $session_setting->schedule_operational_id = $parameters->get('schedule_operational_id');
        $session_setting->start_time = $parameters->get('start_time');
        $session_setting->end_time = $parameters->get('end_time');
        $session_setting->total_budrest = $parameters->get('total_budrest');
        $session_setting->total_target = $parameters->get('total_target');
        $session_setting->max_capacity = $parameters->get('max_capacity');

        $session_setting->save();

        return $session_setting;
    }

    protected function validation($parameters)
    {
        return [
            "schedule_operational_id" => "required|integer",
        ];
    }

}
