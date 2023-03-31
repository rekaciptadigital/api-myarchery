<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ChildrenClassificationMembers;
use App\Models\City;
use App\Models\CityCountry;
use App\Models\Country;
use App\Models\ProvinceCountry;
use App\Models\Provinces;
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
        $classification_club_id = $parameters->get('classification_club_id');
        $classification_country_id = $parameters->get('classification_country_id');
        $classification_province_id = $parameters->get('classification_province_id');
        $classification_city_id = $parameters->get('classification_city_id');
        $classification_children_id = $parameters->get('classification_children_id');

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


        if ($event->parent_classification == 1) {
            if (!empty($classification_club_id)) {
                $club = ArcheryClub::find($classification_club_id);

                if (!$club) {
                    throw new BLoCException("club not found");
                }
            }
        } elseif ($event->parent_classification == 2) {
            $country = Country::find($classification_country_id);

            if (!$country) {
                throw new BLoCException("country not found");
            }
        } elseif ($event->parent_classification == 3) {
            if ($event->classification_country_id == 102) {
                $province = Provinces::find($classification_province_id);
            } else {
                $province = ProvinceCountry::where('id', '=', $classification_province_id)
                    ->where('country_id', '=', $event->classification_country_id)
                    ->first();
            }

            if (!$province) {
                throw new BLoCException("province not found");
            }
        } elseif ($event->parent_classification == 4) {
            if ($event->classification_country_id == 102) {
                $city = City::find($classification_city_id);
            } else {
                $city = CityCountry::where('state_id', '=', $event->province_id)
                    ->where('country_id', '=', $event->classification_country_id)
                    ->where('id', '=', $classification_city_id)
                    ->first();
            }

            if (!$city) {
                throw new BLoCException("city not found");
            }
        } else {
            $check_child = ChildrenClassificationMembers::where('parent_id', '=', $event->parent_classification)
                ->where('id', '=', $classification_children_id)
                ->first();

            if (!$check_child) {
                throw new BLoCException("children classification not found");
            }
        }

        $list_users = [];

        // cek apakah terdapat category individual
        if ($category->count() > 0) {
            foreach ($category as $c) {
                // mengambil participant yang satu grup yang sama dan join di category individual

                $participants = ArcheryEventParticipant::select(
                    "users.*",
                    "cities.name as address_city_name",
                    "provinces.name as address_province_name",
                    "countries.name as country_name",
                    "states.name as province_of_country_name",
                    "cities_of_countries.name as city_of_country_name"
                )
                    ->join("users", "users.id", "=", "archery_event_participants.user_id")
                    ->leftJoin("cities", "cities.id", "=", "users.address_city_id")
                    ->leftJoin("provinces", "provinces.id", "=", "users.address_province_id")
                    ->leftJoin("countries", "countries.id", "=", "users.country_id")
                    ->leftJoin("states", "states.id", "=", "users.province_of_country_id")
                    ->leftJoin("cities_of_countries", "cities_of_countries.id", "=", "users.city_of_country_id")
                    ->where('archery_event_participants.event_category_id', $c->id)
                    ->where('archery_event_participants.status', 1);


                if ($event['parent_classification'] == 1) {
                    if ($classification_club_id) {
                        $participants = $participants->where("archery_event_participants.club_id", '=', $classification_club_id);
                    }
                } elseif ($event['parent_classification'] == 2) {
                    $participants =  $participants->where('archery_event_participants.classification_country_id', '=', $classification_country_id);
                } elseif ($event['parent_classification'] == 3) {
                    $participants =  $participants->where('archery_event_participants.classification_province_id', '=', $classification_province_id);
                } elseif ($event['parent_classification'] == 4) {
                    $participants = $participants->where('archery_event_participants.city_id', '=', $classification_city_id);
                } else {
                    $participants =  $participants->where('archery_event_participants.children_classification', '=', $classification_children_id);
                }

                $participants = $participants->get();
                foreach ($participants as $key => $p) {

                    $country = (object)[];
                    if ($p->is_wna == 0) {
                        $country->id = 102;
                        $country->name = "Indonesia";
                    } else {
                        $country->id = $p->country_id;
                        $country->name = $p->country_name;
                    }

                    $province = (object)[];
                    if ($p->is_wna == 0) {
                        $province->id = (int)$p->address_province_id;
                        $province->name = $p->address_province_name;
                    } else {
                        $province->id = (int)$p->province_of_country_id;
                        $province->name = $p->province_of_country_name;
                    }

                    $city = (object)[];
                    if ($p->is_wna == 0) {
                        if ($p->address_city_id) {
                            $city->id = (int)$p->address_city_id;
                            $city->name = $p->address_city_name;
                        }
                    } else {
                        if ($p->city_of_country_id) {
                            $city->id = (int)$p->city_of_country_id;
                            $city->name = $p->city_of_country_name;
                        }
                    }


                    $response = (object)[];
                    $response->id = $p->id;
                    $response->name = $p->name;
                    $response->email = $p->email;
                    $response->date_of_birth = $p->date_of_birth;
                    $response->gender = $p->gender;
                    $response->province = $province;
                    $response->city = $city;
                    $response->country = $country;
                    $response->is_wna = $p->is_wna;


                    $list_users[] = $response;
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
            // 'club_or_city_id' => 'required',
            "event_id" => "required|exists:archery_events,id"
        ];
    }
}
