<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEventParticipant;
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

        $participant->delete();

        return "success";
    }

    protected function validation($parameters)
    {
        return [
            'participant_id' => 'required',
        ];
    }
}
