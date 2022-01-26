<?php

namespace App\BLoC\Web\ArcheryEventMoreInformation;

use App\Models\ArcheryEventMoreInformation;
use DAI\Utils\Abstracts\Transactional;

class DeleteArcheryEventMoreInformation extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        ArcheryEventMoreInformation::find($parameters->get('id'))->delete();

        return [];
    }

    protected function validation($parameters)
    {
        return [
            'id' => [
                'required',
                'exists:archery_event_more_information',
            ],
        ];
    }
}
