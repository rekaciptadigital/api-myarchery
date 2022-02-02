<?php

namespace App\BLoC\App\ArcheryEvent;

use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetEventCategoryDetail extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user =  $user = Auth::guard('app-api')->user();

        $data = ArcheryEventParticipantMember::join('archery_event_participants', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
            ->join('archery_event_category_details', 'archery_event_category_details.id', '=', 'archery_event_participants.event_category_id')
            ->where('archery_event_participant_members.archery_event_participant_id', $parameters->get('participant_id'))
            ->where('archery_event_participant_members.user_id', $user->id)
            ->where('archery_event_participants.event_category_id', $parameters->get('event_category_id'))
            ->get(['archery_event_category_details.*'])->first();

        if (!$data) {
            throw new BLoCException("event category not found");
        }

        return $data;
    }

    protected function validation($parameters)
    {
        return [
            'event_category_id' => 'required|integer',
            'participant_id' => 'required|integer'
        ];
    }
}
