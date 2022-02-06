<?php

namespace App\BLoC\App\ArcheryEvent;

use App\Models\ArcheryEventParticipant;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class GetParticipantMemberByCategory extends Retrieval
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

        $users = User::join('archery_event_participant_members', 'archery_event_participant_members.user_id', '=', 'users.id')
            ->join('participant_member_teams', 'participant_member_teams.participant_member_id', '=', 'archery_event_participant_members.id')
            ->where('participant_member_teams.participant_id', $participant->id)
            ->get(['users.*']);

        $participant['members'] = $users;

        return $participant;
    }

    protected function validation($parameters)
    {
        return [
            'participant_id' => 'required|integer'
        ];
    }
}
