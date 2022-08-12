<?php

namespace App\BLoC\Web\Enterprise\Venue\Products;

use App\Models\VenuePlace;
use App\Models\VenuePlaceFacility;
use App\Models\VenuePlaceGallery;
use App\Models\VenueMasterPlaceFacility;
use App\Models\Provinces;
use App\Models\City;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;
use App\Libraries\Upload;
use App\Jobs\VenuePlaceSubmissionEmailJob;
use Queue;

class UpdateVenuePlacePricelist extends Transactional
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

        // venue place
        $venue_place->update([
            'eo_id' => $admin->eo_id,
            'weekday_price' => $parameters->get('weekday_price'),
            'weekend_price' => $parameters->get('weekend_price')
        ]);

        $data = VenuePlace::detailVenueById($venue_place->id);

        return $data;
    }

    protected function validation($parameters)
    {
        return [
            "id" => "required|integer",
            "weekday_price" => "required",
            "weekend_price" => "required",
        ];
    }

}