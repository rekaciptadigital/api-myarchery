<?php

namespace App\BLoC\App\ArcheryClub;

use App\Models\Provinces;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetProvince extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        return Provinces::orderBy("name")->limit(10)->offset(0)->get();
    }

    protected function validation($parameters)
    {
        return [
         
        ];
    }
}
