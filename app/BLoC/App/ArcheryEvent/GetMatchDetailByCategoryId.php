<?php

namespace App\BLoC\App\ArcheryEvent;

use App\Models\ArcheryEventParticipant;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class GetMatchDetailByCategoryId extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $participant = ArcheryEventParticipant::find($parameters->get('participant_id'));
        if (!$participant) {
            throw new BLoCException('participant not found');
        }
    }

    protected function validation($parameters)
    {
        return [
            'participant_id' => 'required|integer'
        ];
    }
}
