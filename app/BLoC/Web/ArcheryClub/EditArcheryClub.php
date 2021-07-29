<?php

namespace App\BLoC\Web\ArcheryClub;

use App\Models\ArcheryClub;
use DAI\Utils\Abstracts\Transactional;

class EditArcheryClub extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_club = ArcheryClub::find($parameters->get('id'));
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
            'id' => [
                'required',
                'exists:archery_clubs,id',
            ],
            "name" => "required",
            "address" => "required",
        ];
    }
}
