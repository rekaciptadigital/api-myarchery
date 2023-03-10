<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEventParticipant;
use App\Models\ClassificationEventRegisters;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class DeleteBookingTemporary extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $participant_id = $parameters->get("participant_id");
        $participant = ArcheryEventParticipant::where("status", 6)->where("id", $participant_id)->first();
        if (!$participant) {
            throw new BLoCException("participant not found");
        }
        $id_user = $participant['user_id'];
        $event_id = $participant['event_id'];

        $classification = ClassificationEventRegisters::where('user_id', '=', $id_user)->where('event_id', '=', $event_id)->first();

        $participant->delete();
        $classification->delete();

        return "success";
    }

    protected function validation($parameters)
    {
        return [
            'participant_id' => 'required',
        ];
    }
}
