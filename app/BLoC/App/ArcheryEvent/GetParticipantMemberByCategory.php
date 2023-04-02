<?php

namespace App\BLoC\App\ArcheryEvent;

use App\Models\ArcheryClub;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ChildrenClassificationMembers;
use App\Models\City;
use App\Models\CityCountry;
use App\Models\Country;
use App\Models\ProvinceCountry;
use App\Models\Provinces;
use App\Models\TeamMemberSpecial;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class GetParticipantMemberByCategory extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $participant_id = $parameters->get('participant_id');
        $participant = ArcheryEventParticipant::select(
            "archery_event_participants.*",
            "archery_events.parent_classification",
            "archery_events.classification_country_id as classification_country"
        )
            ->join("archery_events", "archery_events.id", "=", "archery_event_participants.event_id")
            ->where("archery_event_participants.id", $participant_id)
            ->where("archery_event_participants.status", 1)
            ->first();

        $club_id = null;
        $club_name = null;
        $club = ArcheryClub::find($participant->club_id);
        if ($club) {
            $club_id = $club->id;
            $club_name = $club->name;
        }

        $country_id = null;
        $country_name = null;
        $country = Country::find($participant->classification_country_id);
        if ($country) {
            $country_id = $country->id;
            $country_name = $country->name;
        }

        if ($participant->classification_country == 102) {
            $province = Provinces::find($participant->classification_province_id);
            $city = City::find($participant->city_id);
        } else {
            $province = ProvinceCountry::find($participant->classification_province_id);
            $city = CityCountry::find($participant->city_id);
        }

        $province_id = null;
        $province_name = null;
        if ($province) {
            $province_id = $province->id;
            $province_name = $province->name;
        }

        $city_id = null;
        $city_name = null;
        if ($city) {
            $city_id = $city->id;
            $city_name = $city->name;
        }

        $children_classification_member_id = null;
        $children_classification_member_name = null;
        $children_classification = ChildrenClassificationMembers::find($participant->children_classification_id);
        if ($children_classification) {
            $children_classification_member_id = $children_classification->id;
            $children_classification_member_name = $children_classification->title;
        }

        $output = [];

        $user_member = [];
        if ($participant->type == "individual") {
            $user_member = User::find($participant->user_id);
            if (!$user_member) {
                throw new BLoCException("user tidak ditemukan");
            }
            $archery_member = ArcheryEventParticipantMember::where("archery_event_participant_id", $participant->id)->first();
            if (!$archery_member) {
                throw new BLoCException("data member tidak ditemukan");
            }

            $user_member['member_id'] = $archery_member->id;
        } else {
            $gender_category = $participant->team_category_id;
            $participant_individu = ArcheryEventParticipant::select("archery_event_participants.*")
                ->where("archery_event_participants.event_id", $participant->event_id)
                ->where("archery_event_participants.age_category_id", $participant->age_category_id)
                ->where("archery_event_participants.competition_category_id", $participant->competition_category_id)
                ->where("archery_event_participants.distance_id", $participant->distance_id);

            if ($participant->parent_classification == 1) {
                $participant_individu->where("archery_event_participants.club_id", $participant->club_id);
            }

            if ($participant->parent_classification == 2) {
                $participant_individu->where("archery_event_participants.classification_country_id", $participant->classification_country_id);
            }

            if ($participant->parent_classification == 3) {
                $participant_individu->where("archery_event_participants.classification_province_id", $participant->classification_province_id);
            }

            if ($participant->parent_classification == 4) {
                $participant_individu->where("archery_event_participants.city_id", $participant->city_id);
            }

            if ($participant->parent_classification > 5) {
                $participant_individu->where("archery_event_participants.children_classification_id", $participant->children_classification_id);
            }

            $participant_individu = $participant_individu->where("archery_event_participants.status", 1)
                ->where(function ($query) use ($gender_category) {
                    if ($gender_category == "male_team") {
                        $query->where("archery_event_participants.team_category_id", "individu male");
                    }
                    if ($gender_category == "female_team") {
                        $query->where("archery_event_participants.team_category_id", "individu female");
                    }
                    if ($gender_category == "mix_team") {
                        $query->whereIn("archery_event_participants.team_category_id", ["individu male", "individu female"]);
                    }
                })
                ->get();


            if ($participant_individu->count() > 0) {
                foreach ($participant_individu as $ct) {
                    $user = User::find($ct->user_id);
                    if (!$user) {
                        throw new BLoCException("user tidak ada");
                    }

                    $user->participant_id = $ct->id;

                    $check_member_selected_team = TeamMemberSpecial::where("participant_individual_id", $ct->id)
                        ->where("participant_team_id", $participant->id)
                        ->first();
                    $is_selected_for_team = 0;
                    if ($check_member_selected_team) {
                        $is_selected_for_team = 1;
                    }

                    $user->is_selected_for_team = $is_selected_for_team;
                    array_push($user_member, $user);
                }
            }
        }

        $participant['members'] = $user_member;

        $event_category = ArcheryEventCategoryDetail::find($participant->event_category_id);

        $detail_participant_user = User::find($participant->user_id);
        if (!$detail_participant_user) {
            throw new BLoCException("user participant tidak ditemukan");
        }

        $output['participant'] = [
            "participant_id" => $participant->id,
            "event_id" => $participant->event_id,
            "user_id" => $participant->user_id,
            "name" => $detail_participant_user->name,
            "type" => $participant->type,
            "email" => $detail_participant_user->email,
            "phone_number" => $detail_participant_user->phone_number,
            "age" => $detail_participant_user->age,
            "gender" => $detail_participant_user->gender,
            "transaction_log_id" => $participant->transaction_log_id,
            "team_name" => $participant->team_name,
            "city_id" => $participant->city_id,
            "club_id" => $participant->club_id
        ];
        $output['event_category_detail'] = $event_category ? $event_category->getCategoryDetailById($event_category->id) : null;
        $output['member'] = $user_member;
        $output["club"] = $club != null ? $club : [];
        $output['club_id'] = $club_id;
        $output['club_name'] = $club_name;
        $output['country_id'] = $country_id;
        $output["country_name"] = $country_name;
        $output["province_id"] = $province_id;
        $output["province_name"] = $province_name;
        $output["city_id"] = $city_id;
        $output['city_name'] = $city_name;
        $output["children_classification_member_id"] = $children_classification_member_id;
        $output["children_classification_member_name"] = $children_classification_member_name;
        $output["parent_classification_type"] = $participant->parent_classification;

        return $output;
    }

    protected function validation($parameters)
    {
        return [
            'participant_id' => 'required|integer|exists:archery_event_participants,id'
        ];
    }
}
