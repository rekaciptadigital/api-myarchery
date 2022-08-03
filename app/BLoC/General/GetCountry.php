<?php

namespace App\BLoC\General;

use App\Models\Country;
use DAI\Utils\Abstracts\Retrieval;

class GetCountry extends Retrieval
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

        $country = Country::query();
        
        $country->when($name, function ($query) use ($name) {
            return $query->whereRaw("name LIKE ?", ["%" . $name . "%"]);
        });

        return $country->orderBy("name")->limit($limit)->offset($offset)->get();
    }

    protected function validation($parameters)
    {
        return [
            'page' => 'min:1',
            'limit' => 'min:1'
        ];
    }
}
