<?php

namespace App\BLoC\App\ArcheryClub;

use App\Models\City;
use App\Models\Provinces;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetCity extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        return City::where('province_id', $parameters->get('province_id'))->get()->sortBy("name");
    }

    protected function validation($parameters)
    {
        return [
         
        ];
    }
}
