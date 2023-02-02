<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\City;
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
        $category_id = $parameters->get("category_id");
        $event_id = $parameters->get("event_id");
        $club_or_city_id = $parameters->get("club_or_city_id");

        $event = ArcheryEvent::find($event_id);

        $event_category_team = ArcheryEventCategoryDetail::where("id", $category_id)
            ->where("event_id", $event_id)
            ->first();

        if (!$event_category_team) {
            throw new BLoCException("category not found");
        }

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

        if ($event->with_contingent == 1) {
            $city = City::where("id", $club_or_city_id)
                ->where("province_id", $event->province_id)
                ->first();
            if (!$city) {
                throw new BLoCException("city not found");
            }
        } else {
            $club = ArcheryClub::find($club_or_city_id);
            if (!$club) {
                throw new BLoCException("club not found");
            }
        }

        $list_users = [];
        // cek apakah terdapat category individual
        if ($category->count() > 0) {
            foreach ($category as $c) {
                // mengambil participant yang satu grup yang sama dan join di category individual
                $participants = ArcheryEventParticipant::select("users.*")
                    ->join("users", "users.id", "=", "archery_event_participants.user_id")
                    ->where('archery_event_participants.event_category_id', $c->id)
                    ->where('archery_event_participants.status', 1);

                if ($event->with_contingent == 1) {
                    $participants->where("city_id", $club_or_city_id);
                } else {
                    $participants->where("club_id", $club_or_city_id);
                }

                $participants = $participants->get();

                foreach ($participants as $key => $p) {
                    $list_users[] = $p;
                }
            }
        } else {
            throw new BLoCException("category individual not found");
        }

        return $list_users;
    }

    protected function validation($parameters)
    {
        return [
            'category_id' => 'required|exists:archery_event_category_details,id',
            'club_or_city_id' => 'required',
            "event_id" => "required|exists:archery_events,id"
        ];
    }
}
