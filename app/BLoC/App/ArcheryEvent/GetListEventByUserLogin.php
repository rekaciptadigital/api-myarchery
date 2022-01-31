<?php

namespace App\BLoC\App\ArcheryEvent;

use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ClubMember;
use App\Models\ParticipantMemberTeam;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetListEventByUserLogin extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user =  $user = Auth::guard('app-api')->user();
        $data = [];
        $participant_member = ArcheryEventParticipantMember::where('user_id', $user->id)->get();
        if ($participant_member->count() > 0) {
            foreach ($participant_member as $pm) {
                $participant_member_team = ParticipantMemberTeam::where('participant_member_id', $pm->id)->get();
                if ($participant_member_team->count() > 0) {
                    foreach ($participant_member_team as $pmt) {
                        $event_category = ArcheryEventCategoryDetail::find($pmt->event_category_id);
                        $event = ArcheryEvent::find($event_category->event_id);
                        array_push($data, $event);
                    }
                }
            }
        }

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
