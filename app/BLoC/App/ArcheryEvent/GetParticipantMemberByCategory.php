<?php

namespace App\BLoC\App\ArcheryEvent;

use App\Models\ArcheryClub;
use App\Models\ArcheryEventCategoryDetail;
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

        $club = ArcheryClub::find($participant->club_id);

        $output = [];

        $users = User::join('archery_event_participant_members', 'archery_event_participant_members.user_id', '=', 'users.id')
            ->join('participant_member_teams', 'participant_member_teams.participant_member_id', '=', 'archery_event_participant_members.id')
            ->where('participant_member_teams.participant_id', $participant->id)
            ->get(['users.*']);

        $participant['members'] = $users;

        $event_category = ArcheryEventCategoryDetail::find($participant->event_category_id);

        $output['participant'] = [
            "participant_id" => $participant->id,
            "event_id" => $participant->event_id,
            "user_id" => $participant->user_id,
            "name" => $participant->name,
            "type" => $participant->type,
            "email" => $participant->email,
            "phone_number" => $participant->phone_number,
            "age" => $participant->age,
            "gender" => $participant->gender,
            "transaction_log_id" => $participant->transaction_log_id,
            "team_name" => $participant->team_name,
        ];
        $output['event_category_detail'] = $event_category ? $event_category->getCategoryDetailById($event_category->id) : null;
        $output['member'] = $users;
        $output['club'] = $club != null ? $club : [];

        return $output;
    }

    protected function validation($parameters)
    {
        return [
            'participant_id' => 'required|integer'
        ];
    }
}
