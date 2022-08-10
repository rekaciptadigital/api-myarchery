<?php

namespace App\BLoC\Web\Enterprise\Venue\ScheduleOperational;

use App\Models\VenuePlace;
use App\Models\VenuePlaceScheduleOperational;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;

class GetListVenueScheduleOperationalByPlaceId extends Transactional
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

        $schedule_operationals = VenuePlaceScheduleOperational::where('place_id', $parameters->get('place_id'))
                                    ->orderByRaw("FIELD(day, \"Senin\", \"Selasa\", \"Rabu\", \"Kamis\", \"Jumat\", \"Sabtu\", \"Minggu\")")
                                    ->get();
        if (!$schedule_operationals) throw new BLoCException("Data not found");

        return $schedule_operationals;
    }

    protected function validation($parameters)
    {
        return [
            "place_id" => "required|integer"
        ];
    }

}
