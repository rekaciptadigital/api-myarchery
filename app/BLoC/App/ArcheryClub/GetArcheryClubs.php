<?php

namespace App\BLoC\App\ArcheryClub;

use App\Models\ArcheryClub;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetArcheryClubs extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        // return ArcheryClub::withCount('user')->get();
        $archery_clubs = ArcheryClub::query();
        $name = $parameters->get('name');

        $archery_clubs->when($name, function ($query) use ($name) {
            return $query->whereRaw("name LIKE '%" . strtolower($name) . "%'");
        });

        return  $archery_clubs->limit(25)->with('user')->withCount('user')->get();
    }

    protected function validation($parameters)
    {
       return [

       ];
    }
}
