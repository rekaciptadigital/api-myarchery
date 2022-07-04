<?php

namespace App\BLoC\General;

use DAI\Utils\Abstracts\Retrieval;
use App\Libraries\ClubRanked;

class GetEventClubRanked extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get("event_id");
        return ClubRanked::getEventRanked($event_id);
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required'
        ];
    }
}
