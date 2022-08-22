<?php

namespace App\BLoC\App\Enterprise;


use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use App\Models\VenuePlace;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;

class GetDetailVenuePlace extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $venue_place = VenuePlace::find($parameters->get('id'));
        if (!$venue_place) throw new BLoCException("Data not found");
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
