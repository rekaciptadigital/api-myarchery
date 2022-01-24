<?php

namespace App\BLoC\Web\EventOrder;

use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ClubMember;
use App\Models\ParticipantMemberTeam;
use App\Models\User;
use DAI\Utils\Exceptions\BLoCException;

class GetMemberParticipantIndividual extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_category_team = ArcheryEventCategoryDetail::find($parameters->get('category_id'));
        $email = $parameters->get('email');
        if (!$event_category_team) {
            throw new BLoCException("category not found");
        }

        // get user by id
        $user = User::where('email', $email)->first();
        if (!$user) {
            throw new BLoCException("user not found");
        }

        // cek jika user tergabung di club
        $club_member = ClubMember::where('club_id', $parameters->get('club_id'))->where('user_id', $user->id)->first();
        if (!$club_member) {
            throw new BLoCException("member not joined this club");
        }

        // mengambil gender category
        $gender_category = explode('_', $event_category_team->team_category_id)[0];

        // mengambil category individu yang satu grup dengan team berdasarkan gender
        $category = ArcheryEventCategoryDetail::where('event_id', $event_category_team->event_id)
            ->where('age_category_id', $event_category_team->age_category_id)
            ->where('competition_category_id', $event_category_team->competition_category_id)
            ->where('distance_id', $event_category_team->distance_id)
            ->where('team_category_id', $gender_category == 'mix' ? 'individu_' . $user->gender : 'individu_' . $gender_category)
            ->first();

        // cek apakah terdapat category individual
        if ($category) {
            // mengambil participant yang satu grup yang sama dan join di category individual
            $participant = ArcheryEventParticipant::where('event_category_id', $category->id)
                ->where('user_id', $user->id)
                ->where('club_id', $club_member->club_id)->where('status', 1)->first();
        } else {
            throw new BLoCException("category individual not found");
        }

        // cek apakah terdapat participant
        if (!$participant) {
            throw new BLoCException('this user not join the individual category with this club');
        }

        // cek apakah user telah bergabung di category ini sebelumnya
        $participant_member =  ArcheryEventParticipantMember::where('archery_event_participant_id', $participant->id)->first();
        if (!$participant_member) {
            throw new BLoCException(" user not join this member for this category");
        }
        $participant_member_team = ParticipantMemberTeam::where('participant_member_id', $participant_member->id)->where('event_category_id', $event_category_team->id)->first();
        if ($participant_member_team) {
            throw new BLoCException("this user already join this category");
        }


        return $user;
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
