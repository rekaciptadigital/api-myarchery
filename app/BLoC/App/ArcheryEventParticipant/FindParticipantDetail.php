<?php

namespace App\BLoC\App\ArcheryEventParticipant;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventParticipant;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Auth;

class FindParticipantDetail extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $unique_id = $parameters->get('unique_id');
        $participant = ArcheryEventParticipant::where('unique_id', $unique_id)->first();
        $participant->archeryEventParticipantMembers;
        $participant->archeryEvent = BLoC::call('findArcheryEvent', ['id' => $participant->event_id]);

        return $participant;
    }

    protected function validation($parameters)
    {
        return [
            'unique_id' => 'required|exists:archery_event_participants,unique_id',
        ];
    }
}
