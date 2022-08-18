<?php

namespace App\BLoC\App\UserAuth;

use App\Libraries\Upload;
use App\Models\City;
use App\Models\CityCountry;
use App\Models\Country;
use App\Models\Provinces;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateUserProfile extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user_login = Auth::guard('app-api')->user();
        $name = $parameters->get("name");
        $place_of_birth = $parameters->get('place_of_birth');
        $gender = $parameters->get('gender');
        $date_of_birth = $parameters->get('date_of_birth');
        $phone_number = $parameters->get('phone_number');
        $user_id = $parameters->get('user_id');

        $user = User::find($user_id);

        if (!$user) {
            throw new BLoCException("user not found");
        }

        if ($user_login->id !== $user->id) {
            throw new BLoCException("forbiden");
        }

        $is_updated = 0;
        $can_update_name = $user->can_update_name;
        if ($user->name != $name) {
            if ($can_update_name == 0) {
                throw new BLoCException("kuota update nama sudah habis");
            }
            $user->name = $name;
            $can_update_name = $can_update_name - 1;
            $user->can_update_name = $can_update_name;
            $is_updated = 1;
        }

        $can_update_gender = $user->can_update_gender;
        if ($user->gender != $gender) {
            if ($can_update_gender == 0) {
                throw new BLoCException("kuota update gender sudah habis");
            }
            $user->gender = $gender;
            $can_update_gender = $can_update_gender - 1;
            $user->can_update_gender = $can_update_gender;
            $is_updated = 1;
        }

        $can_update_date_of_birth = $user->can_update_date_of_birth;
        if ($user->place_of_birth != $place_of_birth || $user->date_of_birth != $date_of_birth) {
            if ($can_update_gender == 0) {
                throw new BLoCException("kuota update ttl sudah habis");
            }
            $user->place_of_birth = $place_of_birth;
            $user->date_of_birth = $date_of_birth;
            $can_update_date_of_birth = $can_update_date_of_birth - 1;
            $user->can_update_date_of_birth = $can_update_date_of_birth;
            $is_updated = 1;
        }

        if ($is_updated == 1) {

            $user->verify_status = 4;
            $user->date_verified = null;
            $user->reason_rejected = null;


            $user->is_wna = 0;
            $user->address = null;

            $user->nik = null;
            $user->address_province_id  = null;
            $user->address_city_id  = null;
            $user->ktp_kk = null;

            $user->passport_number  = null;
            $user->country_id = 0;
            $user->city_of_country_id = 0;
            $user->passport_img = null;
        }

        $user->phone_number = $phone_number;
        $user->save();

        return User::getDetailUser($user_id);
    }

    protected function validation($parameters)
    {
        return [
            "user_id" => 'required|integer',
            'name' => 'sometimes|required|min:1|max:255',
            'date_of_birth' => 'sometimes|required|date',
            'gender' => 'sometimes|required|in:male,female',
            'phone_number' => 'sometimes|required|numeric|unique:users,phone_number,' . $parameters->get("user_id"),
            "place_of_birth" => "sometimes|required|string|min:1"
        ];
    }
}
