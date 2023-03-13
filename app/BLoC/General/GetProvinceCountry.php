<?php

namespace App\BLoC\General;

use App\Models\ProvinceCountry;
use DAI\Utils\Abstracts\Retrieval;

class GetProvinceCountry extends Retrieval
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
        $name = $parameters->get("name");
        $country_id = $parameters->get("country_id");

        $provinceCountry = ProvinceCountry::query();

        $provinceCountry->when($country_id, function ($query) use ($country_id) {
            return $query->where("country_id", $country_id);
        });

        $provinceCountry->when($name, function ($query) use ($name) {
            return $query->whereRaw("name LIKE ?", ["%" . $name . "%"]);
        });

        return $provinceCountry->orderBy("name")->limit($limit)->offset($offset)->get();
    }

    protected function validation($parameters)
    {
        return [
            'page' => 'min:1',
            'limit' => 'min:1'
        ];
    }
}
