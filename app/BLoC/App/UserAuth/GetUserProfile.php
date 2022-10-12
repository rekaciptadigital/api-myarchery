<?php

namespace App\BLoC\App\UserAuth;

use App\Models\City;
use App\Models\Provinces;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;

class GetUserProfile extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = Auth::guard('app-api')->user();
        $output = [
            "id" => $user->id,
            "name" => $user->name,
            "email" => $user->email,
            "phone_number" => $user->phone_number,
            "avatar" => $user->avatar,
            "date_of_birth" => $user->date_of_birth,
            "gender" => $user->gender,
            "address" => $user->address,
            "place_of_birth" => $user->place_of_birth,
            "verify_status" => $user->verify_status,
            "address_province" => Provinces::find($user->address_province_id),
            "address_city" => City::find($user->address_city_id),
            "status_verify" => $user->status_verify,
            "reason_rejected" => $user->reason_rejected,
            "date_verified" => $user->date_verified,
            "age" => $user->age,
            "can_update_name" => $user->can_update_name,
            "can_update_date_of_birth" => $user->can_update_date_of_birth,
            "can_update_gender" => $user->can_update_gender,
            "is_wna" => $user->is_wna
        ];

        return $output;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
