<?php

namespace App\BLoC\App\UserAuth;

use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
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

        $data = $parameters->all();
        // return $data;

        $user->fill($data);
        $user->save();
        return $user;
    }

    protected function validation($parameters)
    {
        return [
            "user_id" => 'required|integer',
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users',
            'date_of_birth' => 'date',
            'gender' => 'in:male,female',
            'address' => 'string',
            'place_of_birth' => 'string',
            'address_province_id' => 'integer',
            'address_city_id' => 'integer',
        ];
    }
}
