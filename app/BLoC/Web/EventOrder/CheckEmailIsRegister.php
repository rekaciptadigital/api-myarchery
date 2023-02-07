<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantNumber;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Abstracts\Transactional;
use App\Libraries\PaymentGateWay;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventEmailWhiteList;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\ArcheryEventQualificationTime;
use App\Models\ClubMember;
use App\Models\ParticipantMemberTeam;
use App\Models\ArcheryEventParticipantMemberNumber;
use App\Models\ArcheryMasterAgeCategory;
use App\Models\TemporaryParticipantMember;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\ArcherySeriesUserPoint;
use App\Models\City;
use DateTime;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redis;

class CheckEmailIsRegister extends Transactional
{
    public function getDescription()
    {
        return "";
        /*
            # order individu
                db insert :
                            - archery_event_participants
                            - archery_event_participant_members
                            - transaction_log (for have fee)
                            - archery_event_participant_member_numbers (if free)
                            - archery_event_qualification_schedule_full_days (if free)
                            - participant_member_team (if free)
        */
    }

    protected function process($parameters)
    {
        $emails = $parameters->get("emails");
        $data = [];
        foreach ($emails as $key => $e) {
            $user = User::select(
                "users.*",
                "cities.name as address_city_name",
                "provinces.name as address_province_name",
                "countries.name as country_name",
                "states.name as province_of_country_name",
                "cities_of_countries.name as city_of_country_name"
            )
                ->leftJoin("cities", "cities.id", "=", "users.address_city_id")
                ->leftJoin("provinces", "provinces.id", "=", "users.address_province_id")
                ->leftJoin("countries", "countries.id", "=", "users.country_id")
                ->leftJoin("states", "states.id", "=", "users.province_of_country_id")
                ->leftJoin("cities_of_countries", "cities_of_countries.id", "=", "users.city_of_country_id")
                ->where("email", $e)
                ->first();

            if ($user) {

                $country = (object)[];
                if ($user->is_wna == 0) {
                    $country->id = 102;
                    $country->name = "Indonesia";
                } else {
                    $country->id = $user->country_id;
                    $country->name = $user->country_name;
                }

                $province = (object)[];
                if ($user->is_wna == 0) {
                    $province->id = (int)$user->address_province_id;
                    $province->name = $user->address_province_name;
                } else {
                    $province->id = (int)$user->province_of_country_id;
                    $province->name = $user->province_of_country_name;
                }

                $city = (object)[];
                if ($user->is_wna == 0) {
                    $city->id = (int)$user->address_city_id;
                    $city->name = $user->address_city_name;
                } else {
                    $city->id = (int)$user->city_of_country_id;
                    $city->name = $user->city_of_country_name;
                }

                $response = (object)[];
                $response->id = $user->id;
                $response->name = $user->name;
                $response->email = $user->email;
                $response->gender = $user->gender;
                $response->date_of_birth = $user->date_of_birth;
                $response->country = $country;
                $response->province = $province;
                $response->city = $city;
                $response->is_wna = $user->is_wna;
                $data[] = (object)[
                    "data" => $response,
                    "message" => "email " . $e . " sudah terdaftar sebagai user"
                ];
            } else {
                $data[] = (object)[
                    "data" => null,
                    "message" => "email " . $e . " belum terdaftar sebagai user"
                ];
            }
        }

        return $data;
    }

    protected function validation($parameters)
    {
        return [
            "emails" => "required|array",
            "emails.*" => "required|email"
        ];
    }
}
