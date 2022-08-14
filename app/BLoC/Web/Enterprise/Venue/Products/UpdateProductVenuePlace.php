<?php

namespace App\BLoC\Web\Enterprise\Venue\Products;

use App\Models\VenuePlace;
use App\Models\VenuePlaceProduct;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;

class UpdateProductVenuePlace extends Transactional
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
        if ($venue_place->eo_id != $admin->eo_id) throw new BLoCException("You're not the owner of this venue");

        $product->update([
            'place_id' => $product->place_id,
            'product_name' => $parameters->get('product_name'),
            'description' => $parameters->get('description'),
            'base_product' => $parameters->get('base_product'),
            'total_each_rent_per_day' => $parameters->get('total_each_rent_per_day') ?? 1,
            'weekday_price' => $parameters->get('weekday_price'),
            'weekend_price' => $parameters->get('weekend_price'),
            'has_session' => $parameters->get('has_session'),
        ]);

        return $product;
    }

    protected function validation($parameters)
    {
        return [
            "id" => "required|integer",
            "product_name" => "required",
            "base_product" => "required|in:Arrow,Target,Bantalan,Orang,Hari,Jam",
            "total_each_rent_per_day" => "required",
            "weekday_price" => "required",
            "weekend_price" => "required",
            "has_session" => "required"
        ];
    }

}