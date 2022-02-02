<?php

namespace App\BLoC\App\ArcheryEvent;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ParticipantMemberTeam;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;

class GetListEventByUserLogin extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $limit = !empty($parameters->get('limit')) ? $parameters->get('limit') : 1;
        $page = $parameters->get('page');
        $offset = ($page - 1) * $limit;

        $user =  $user = Auth::guard('app-api')->user();


        $data = ParticipantMemberTeam::join('archery_event_participant_members', 'archery_event_participant_members.id', '=', 'participant_member_teams.participant_member_id')
            ->join('archery_event_category_details', 'archery_event_category_details.id', '=', 'participant_member_teams.event_category_id')
            ->join('archery_events', 'archery_events.id', '=', 'archery_event_category_details.event_id')
            ->where('archery_event_participant_members.user_id', $user->id)
            ->distinct()
            ->limit($limit)
            ->offset($offset)
            ->get(['archery_events.*']);

        return $data;
    }

    protected function validation($parameters)
    {
        return [
            'page' => 'min:1',
            'limit' => 'min:1'
        ];
    }
}
