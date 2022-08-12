<?php

namespace App\BLoC\General;

use App\Models\CityCountry;
use DAI\Utils\Abstracts\Retrieval;

class GetCityCountry extends Retrieval
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
        $country_id = $parameters->get('country_id');
        $name = $parameters->get("name");

        $city_country = CityCountry::query();

        $city_country->when($country_id, function ($query) use ($country_id) {
            return $query->where("country_id", $country_id);
        });

        $city_country->when($name, function ($query) use ($name) {
            return $query->whereRaw("name LIKE ?", ["%" . $name . "%"]);
        });

        return $city_country->orderBy('name')->limit($limit)->offset($offset)->get();
    }

    protected function validation($parameters)
    {
        return [];
    }
}
