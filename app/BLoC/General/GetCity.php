<?php

namespace App\BLoC\General;

use App\Models\City;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class GetCity extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $limit = !empty($parameters->get('limit')) ? $parameters->get('limit') : 1;
        $page = $parameters->get('page');
        $offset = ($page - 1) * $limit;
        $city = $parameters->get('province_id') ? City::where('province_id', $parameters->get('province_id'))->orderBy('name')->get() : City::orderBy('name')->limit($limit)->offset($offset)->get();
        if(!$city){
            throw new BLoCException('data not found');
        }
        return $city;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
