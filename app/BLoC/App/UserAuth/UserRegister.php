<?php

namespace App\BLoC\App\UserAuth;

use App\Models\ArcheryClub;
use App\Models\User;
use App\Models\UserArcheryInfo;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserRegister extends Transactional
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
            'gender' => $parameters->get('gender'),
            'phone_number' => $parameters->get('phone_number'),
        ]);

        $token = Auth::guard('app-api')->setTTL(60 * 24 * 7)->attempt([
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
            'gender' => 'required|in:male,female',
        ];
    }
}
