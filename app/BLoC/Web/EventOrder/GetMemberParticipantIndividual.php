<?php

namespace App\BLoC\Web\EventOrder;

use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
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
        // cari event category detail team berdasarkan id yang diinputkan user
        $event_category_team = ArcheryEventCategoryDetail::find($parameters->get('category_id'));
        if (!$event_category_team) {
            throw new BLoCException("category not found");
        }
        
        $email = $parameters->get('email');

        if ($event_category_team->category_team == 'individual') {
            throw new BLoCException("this category must be team category");
        }


        // mengambil gender category
        $gender_category = $event_category_team->gender_category;

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
                    $user = User::find($p->user_id);
                    $p->toArray();
                    $p['avatar'] = $user->avater;
                    array_push($array_participant, $p);
                }
            }
        } else {
            throw new BLoCException("category individual not found");
        }
        
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
