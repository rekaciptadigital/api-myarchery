<?php

namespace App\BLoC\General;

use App\Models\Provinces;
use DAI\Utils\Abstracts\Retrieval;

class GetProvince extends Retrieval
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
        $province = Provinces::orderBy("name")->limit($limit)->offset($offset)->get();

        return $province;
    }

    protected function validation($parameters)
    {
        return [
            'page' => 'min:1',
            'limit' => 'min:1'
        ];
    }
}
