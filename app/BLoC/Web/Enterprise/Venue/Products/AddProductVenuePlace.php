<?php

namespace App\BLoC\Web\Enterprise\Venue\Products;

use App\Models\VenuePlace;
use App\Models\VenuePlaceProduct;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;

class AddProductVenuePlace extends Transactional
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

        $product = new VenuePlaceProduct();
        $product->place_id = $parameters->get('place_id');
        $product->product_name = $parameters->get('product_name');
        $product->description = $parameters->get('description');
        $product->base_product = $parameters->get('base_product');
        $product->total_each_rent_per_day = $parameters->get('total_each_rent_per_day') ?? 1;
        $product->weekday_price = $parameters->get('weekday_price');
        $product->weekend_price = $parameters->get('weekend_price');
        $product->has_session = $parameters->get('has_session');

        $product->save();

        return $product;
    }

    protected function validation($parameters)
    {
        return [
            "place_id" => "required|integer",
            "product_name" => "required",
            "base_product" => "required|in:Arrow,Target,Bantalan,Orang,Hari,Jam",
            "total_each_rent_per_day" => "required",
            "weekday_price" => "required",
            "weekend_price" => "required",
            "has_session" => "required"
        ];
    }

}