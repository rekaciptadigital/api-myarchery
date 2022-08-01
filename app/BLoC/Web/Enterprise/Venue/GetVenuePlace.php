<?php

namespace App\BLoC\Web\Enterprise\Venue;


use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use App\Models\VenuePlace;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;

class GetVenuePlace extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $venue_place = VenuePlace::find($parameters->get('id'));
        if (!$venue_place) throw new BLoCException("Data not found");
        if ($venue_place->eo_id != $admin->eo_id) throw new BLoCException("You're not the owner of this event");
        $venue_place_detail = VenuePlace::detailVenueById($parameters->get('id'));

        return $venue_place_detail;
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|integer',
        ];
    }
}
