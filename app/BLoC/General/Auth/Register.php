<?php

namespace App\BLoC\General\Auth;

use App\Models\User;
use App\Models\UserRole;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Register extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = User::create([
            'name' => $parameters->get('name'),
            'email' => $parameters->get('email'),
            'password' => Hash::make($parameters->get('password')),
            'date_of_birth' => $parameters->get('date_of_birth'),
            'place_of_birth' => $parameters->get('place_of_birth'),
            'phone_number' => $parameters->get('phone_number'),
        ]);

        $user_role = new UserRole();
        $user_role->user_id = $user->id;
        $user_role->role_id = 2;
        $user_role->save();

        $token = Auth::setTTL(60 * 24 * 7)->attempt([
            'email' => $parameters->get('email'),
            'password' => $parameters->get('password'),
        ]);
        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::factory()->getTTL()
        ];
    }

    protected function validation($parameters)
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'date_of_birth' => 'required|date',
            'place_of_birth' => 'required|string',
            'phone_number' => 'required|string',
        ];
    }
}
