<?php

namespace App\BLoC\App\Enterprise;


use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use App\Models\VenuePlaceProduct;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;

class GetListProductByVenuePlace extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $venue_place_products = VenuePlaceProduct::where('place_id', $parameters->get('id'))->get();
        if (!$venue_place_products) throw new BLoCException("Data not found");

        return $venue_place_products;
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|integer',
        ];
    }
}
