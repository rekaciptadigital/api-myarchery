<?php

namespace App\BLoC\Web\ArcheryClub;

use App\Models\ArcheryClub;
use DAI\Utils\Abstracts\Transactional;

class DeleteArcheryClub extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        ArcheryClub::find($parameters->get('id'))->delete();

        return [];
    }

    protected function validation($parameters)
    {
        return [
            'id' => [
                'required',
                'exists:archery_clubs',
            ],
        ];
    }
}
