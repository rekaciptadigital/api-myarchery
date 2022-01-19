<?php

namespace App\BLoC\Web\EventOrder;

use App\Libraries\PaymentGateWay;
use App\Models\ArcheryClub;
use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\TransactionLog;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ClubMember;
use App\Models\User;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetMemberParticipantIndividual extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_category_detail = ArcheryEventCategoryDetail::find($parameters->get('category_id'));
        $email = $parameters->get('email');
        if (!$event_category_detail) {
            throw new BLoCException("category not found");
        }

        $club_member = ClubMember::where('club_id', $parameters->get('club_id'))->where('email', $email)->first();
        if (!$club_member) {
            throw new BLoCException("member not joined this club");
        }

        $gender_category = explode('_', $event_category_detail->team_category_id)[0];
        $category = ArcheryEventCategoryDetail::where('event_id', $event_category_detail->event_id)
            ->where('age_category_id', $event_category_detail->age_category_id)
            ->where('competition_category_id', $event_category_detail->competition_category_id)
            ->where('distance_id', $event_category_detail->distance_id)->where('team_category_id', 'individu_' . $gender_category)->first();


        if ($category) {
            $participant = ArcheryEventParticipant::where('event_category_id', $category->id)
                ->where('email', $email)
                ->where('club', $club_member->club_id)->where('status', 1)->first();
        } else {
            throw new BLoCException("category individual not found");
        }

        if (!$participant) {
            throw new BLoCException('you are not join the individual category');
        }
    }

    protected function validation($parameters)
    {
        return [
            'category_id' => 'required',
            'email' => 'required',
            'club_id' => 'required'
        ];
    }
}
