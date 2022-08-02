<?php

namespace App\BLoC\Web\Enterprise\Venue\ScheduleOperational;

use App\Models\VenuePlace;
use App\Models\VenuePlaceScheduleOperational;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;

class AddVenueScheduleOperational extends Transactional
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

        $schedule_operational = new VenuePlaceScheduleOperational();
        $schedule_operational->place_id = $parameters->get('place_id');
        $schedule_operational->day = $parameters->get('day');
        $schedule_operational->open_time = $parameters->get('open_time') ?? null;
        $schedule_operational->closed_time = $parameters->get('closed_time') ?? null;
        $schedule_operational->start_break_time = $parameters->get('start_break_time') ?? null;
        $schedule_operational->end_break_time = $parameters->get('end_break_time') ?? null;
        $schedule_operational->is_open = $parameters->get('is_open');
        $schedule_operational->save();

        return $schedule_operational;
    }

    protected function validation($parameters)
    {
        return [
            "place_id" => "required|integer",
            "day" => "required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu",
        ];
    }

}
