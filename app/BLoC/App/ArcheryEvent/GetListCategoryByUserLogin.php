<?php

namespace App\BLoC\App\ArcheryEvent;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetListCategoryByUserLogin extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user =  $user = Auth::guard('app-api')->user();

        $event = ArcheryEvent::find($parameters->get('event_id'));
        if (!$event) {
            throw new BLoCException("event not found");
        }

        $data = ArcheryEventCategoryDetail::join('participant_member_teams', 'participant_member_teams.event_category_id', '=', 'archery_event_category_details.id')
            ->join('archery_event_participant_members', 'archery_event_participant_members.id', '=', 'participant_member_teams.participant_member_id')
            ->where('archery_event_participant_members.user_id', $user->id)
            ->where('archery_event_category_details.event_id', $event->id)
            ->get(['archery_event_category_details.*']);

        return $data;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required|integer'
        ];
    }
}
