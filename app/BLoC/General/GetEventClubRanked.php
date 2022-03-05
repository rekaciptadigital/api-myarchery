<?php

namespace App\BLoC\General;

use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use DAI\Utils\Helpers\BLoC;
use DAI\Utils\Exceptions\BLoCException;
use App\Libraries\ClubRanked;

class GetEventClubRanked extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        return ClubRanked::getEventRanked($parameters->get("event_id"));
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required'
        ];
    }
}
