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
        $province_id = $parameters->get('province_id');

        $name = $parameters->get('name');

        $city = City::query();

        $city->when($name, function ($query) use ($name) {
            return $query->whereRaw("name LIKE ?", ["%" . $name . "%"]);
        });

        $city->when($province_id, function ($query) use ($province_id) {
            return $query->where("province_id", $province_id);
        });

        return $city->orderBy('name')->limit($limit)->offset($offset)->get();
    }

    protected function validation($parameters)
    {
        return [];
    }
}
