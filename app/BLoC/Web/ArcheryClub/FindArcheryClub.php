<?php

namespace App\BLoC\Web\ArcheryClub;

use App\Models\ArcheryClub;
use DAI\Utils\Abstracts\Retrieval;

class FindArcheryClub extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_club = ArcheryClub::find($parameters->get('id'));

        return $archery_club;
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|exists:archery_clubs,id',
        ];
    }
}