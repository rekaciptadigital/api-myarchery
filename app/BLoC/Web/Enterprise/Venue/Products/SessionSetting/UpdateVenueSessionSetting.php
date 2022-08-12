<?php

namespace App\BLoC\Web\Enterprise\Venue\Products\SessionSetting;

use App\Models\VenuePlace;
use App\Models\VenuePlaceScheduleOperationalSession;
use App\Models\VenuePlaceScheduleOperational;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;

class UpdateVenueSessionSetting extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
    
        $session_setting = VenuePlaceScheduleOperationalSession::find($parameters->get('id'));
        if (!$session_setting) throw new BLoCException("Data not found");

        $schedule_operational = VenuePlaceScheduleOperational::find($session_setting->schedule_operational_id);
        $venue_place = VenuePlace::find($schedule_operational->place_id);
        if ($venue_place->eo_id != $admin->eo_id) throw new BLoCException("You're not the owner of this event");

        $session_setting->update([
            'schedule_operational_id' => $session_setting->schedule_operational_id,
            'start_time' => $parameters->get('start_time'),
            'end_time' => $parameters->get('end_time'),
            'total_budrest' => $parameters->get('total_budrest'),
            'total_target' => $parameters->get('total_target'),
            'max_capacity' => $parameters->get('max_capacity')
        ]);

        return $session_setting;
    }

    protected function validation($parameters)
    {
        return [
            "id" => "required|integer"
        ];
    }

}
