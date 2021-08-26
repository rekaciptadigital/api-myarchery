<?php

namespace App\BLoC\Web\ArcheryEvent;

use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Retrieval;

class FindArcheryEvent extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_event = ArcheryEvent::find($parameters->get('id'));
        $archery_event->archeryEventCategories;

        return $archery_event;
    }

    protected function validation($archery_event)
    {
        return [
            'id' => 'required|exists:roles,id',
        ];
    }
}