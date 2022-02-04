<?php

namespace App\BLoC\App\ArcheryEvent;

use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryMasterAgeCategory;
use App\Models\ArcheryMasterCompetitionCategory;
use App\Models\ArcheryMasterDistanceCategory;
use App\Models\ArcheryMasterTeamCategory;
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
            ->join('archery_event_participants', 'archery_event_participants.id', '=', 'participant_member_teams.participant_id')
            ->where('archery_event_participant_members.user_id', $user->id)
            ->where('archery_event_category_details.event_id', $event->id)
            ->get(['archery_event_category_details.*']);

            return $data;

        if ($data->count() > 0) {
            foreach ($data as $d) {
                $event_category = ArcheryEventCategoryDetail::find($d->event_category_id);
                $club = ArcheryClub::find($d->club_id);
                $d['club_detail'] = $club;
                $d['event_category_detail'] = $event_category;
                $d['event_category_detail']['age_category_detail'] = ArcheryMasterAgeCategory::find($event_category->age_category_id);
                $d['event_category_detail']['distance_category_detail'] = ArcheryMasterDistanceCategory::find($event_category->distance_id);
                $d['event_category_detail']['competition_category_detail'] = ArcheryMasterCompetitionCategory::find($event_category->competition_category_id);
                $d['event_category_detail']['team_category_detail'] = ArcheryMasterTeamCategory::find($event_category->team_category_id);
            }
        }

        return $data;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required|integer'
        ];
    }
}
