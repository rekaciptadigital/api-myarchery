<?php

namespace App\BLoC\Web\ArcheryClub;

use App\Models\ArcheryClub;
use DAI\Utils\Abstracts\Transactional;

class AddArcheryClub extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_club = new ArcheryClub();
        $archery_club->name = $parameters->get('name');
        $archery_club->address = $parameters->get('address');
        $archery_club->latitude = $parameters->get('latitude');
        $archery_club->longitude = $parameters->get('longitude');
        $archery_club->location_type = $parameters->get('location_type');
        $archery_club->save();

        return $archery_club;
    }

    protected function validation($parameters)
    {
        return [
            "name" => "required",
            "address" => "required",
            "location_type" => "in:Indoor,Outdoor,Indoor/Outdoor"
        ];
    }
}
