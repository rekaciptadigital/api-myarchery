<?php

namespace App\BLoC\App\UserAuth;

use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Auth;

class UpdateUserProfile extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user_login = Auth::guard('app-api')->user();

        $user_id = $parameters->get('user_id');
        $user = User::find($user_id);

        if (!$user) {
            throw new BLoCException("user not found");
        }

        if ($user_login->id !== $user->id) {
            throw new BLoCException("forbiden");
        }

        if (
            $parameters->get('name')
            || $parameters->get('place_of_birth')
            || $parameters->get('gender')
            || $parameters->get("date_of_birth")
        ) {
            if ($user->verify_status == 1) {
                throw new BLoCException("tidak dapat mengubah data karena status anda telah terverifikasi");
            } else {
                $user->name = $parameters->get("name");
                $user->place_of_birth = $parameters->get('place_of_birth');
                $user->gender = $parameters->get('gender');
                $user->date_of_birth = $parameters->get('date_of_birth');
            }
        }


        $user->phone_number = $parameters->get('phone_number');
        $user->address = $parameters->get('address');
        $user->save();

        return $user;
    }

    protected function validation($parameters)
    {
        return [
            "user_id" => 'required|integer',
            'name' => 'string|max:255',
            'date_of_birth' => 'date',
            'gender' => 'in:male,female',
            'address' => 'string',
            'place_of_birth' => 'string',
            'phone_number' => 'string'
        ];
    }
}
