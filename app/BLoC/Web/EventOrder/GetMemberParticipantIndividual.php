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

        // return User::whereRaw("name LIKE ?", ["%" . $parameters->get('email') . "%"])->get();
        $event_category_team = ArcheryEventCategoryDetail::find($parameters->get('category_id'));
        $email = $parameters->get('email');
        if (!$event_category_team) {
            throw new BLoCException("category not found");
        }

        // get user by id
        // $user = User::where('email', $email)->first();
        // if (!$user) {
        //     throw new BLoCException("user not found");
        // }

        // cek jika user tergabung di club
        // $club_member = ClubMember::where('club_id', $parameters->get('club_id'))->where('user_id', $user->id)->first();
        // if (!$club_member) {
        //     throw new BLoCException("member not joined this club");
        // }

        // mengambil gender category
        $gender_category = explode('_', $event_category_team->team_category_id)[0];

        // mengambil category individu yang satu grup dengan team berdasarkan gender
        $category = ArcheryEventCategoryDetail::where('event_id', $event_category_team->event_id)
            ->where('age_category_id', $event_category_team->age_category_id)
            ->where('competition_category_id', $event_category_team->competition_category_id)
            ->where('distance_id', $event_category_team->distance_id)
            ->where(function ($query) use ($gender_category) {
                if ($gender_category == 'mix') {
                    return $query->where('team_category_id', 'individu male')->orWhere('team_category_id', 'individu female');
                } else {
                    return $query->where('team_category_id', 'individu ' . $gender_category);
                }
            })->get();


        $array_participant = [];
        // cek apakah terdapat category individual
        if ($category->count() > 0) {
            foreach ($category as $c) {
                // mengambil participant yang satu grup yang sama dan join di category individual
                $participants = ArcheryEventParticipant::where('event_category_id', $c->id)
                    ->whereRaw("email LIKE ?", ["%" . $email . "%"])
                    ->where('club_id', $parameters->club_id)->where('status', 1)->get();
                foreach ($participants as $p) {
                    array_push($array_participant, $p);
                }
            }
        } else {
            throw new BLoCException("category individual not found");
        }


        // foreach ($array_participant as $ap) {
        //     // cek apakah user telah bergabung di category ini sebelumnya
        //     $participant_member =  ArcheryEventParticipantMember::where('archery_event_participant_id', $ap->id)->first();
        //     if (!$participant_member) {
        //         throw new BLoCException(" user not join this member for this category");
        //     }

        //     // return $participant_member;

        //     $participant_member_team = ParticipantMemberTeam::where('participant_member_id', $participant_member->id)->where('event_category_id', $event_category_team->id)->first();
        //     // return $participant_member_team;
        //     if ($participant_member_team) {
        //         if (($key = array_search($ap, $array_participant)) !== false) {
        //             // return $array_participant[$key];
        //             unset($array_participant[$key]);
        //         }
        //     }
        // }

        // return array_values($array_participant);


        return $array_participant;
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
