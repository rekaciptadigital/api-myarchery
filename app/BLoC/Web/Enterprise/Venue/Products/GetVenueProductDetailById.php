<?php

namespace App\BLoC\Web\Enterprise\Venue\Products;

use App\Models\VenuePlace;
use App\Models\VenuePlaceProduct;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;

class GetVenueProductDetailById extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $product = VenuePlaceProduct::find($parameters->get('id'));
        if (!$product) throw new BLoCException("Data not found");

        $venue_place = VenuePlace::find($product->place_id);
        if ($venue_place->eo_id != $admin->eo_id) throw new BLoCException("You're not the owner of this event");

        return $product;
    }

    protected function validation($parameters)
    {
        return [
            "id" => "required|integer"
        ];
    }

}
