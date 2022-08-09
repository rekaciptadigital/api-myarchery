<?php

namespace App\BLoC\Web\Enterprise\Venue\ScheduleOperational;

use App\Models\VenuePlace;
use App\Models\VenuePlaceScheduleOperational;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;

class UpdateVenueScheduleOperational extends Transactional
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
        if (!$venue_place) throw new BLoCException("Data not found");
        if ($venue_place->eo_id != $admin->eo_id) throw new BLoCException("You're not the owner of this event");

        $schedule_operational->update([
            'place_id' => $schedule_operational->place_id,
            'day' => $parameters->get('day'),
            'open_time' => $parameters->get('open_time') ?? null,
            'closed_time' => $parameters->get('closed_time') ?? null,
            'start_break_time' => $parameters->get('start_break_time') ?? null,
            'end_break_time' => $parameters->get('end_break_time') ?? null,
            'is_open' => $parameters->get('is_open')
        ]);

        return $schedule_operational;
    }

    protected function validation($parameters)
    {
        return [
            "id" => "required|integer",
            "day" => "required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu",
        ];
    }

}
