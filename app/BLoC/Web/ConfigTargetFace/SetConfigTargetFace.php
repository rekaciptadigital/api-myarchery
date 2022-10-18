<?php

namespace App\BLoC\Web\ConfigTargetFace;

use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Retrieval;

class SetConfigTargetFace extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get("event_id");
        $event = ArcheryEvent::find($event_id);

        // reset config
        
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required|exists:archery_events,id',
        ];
    }
}
