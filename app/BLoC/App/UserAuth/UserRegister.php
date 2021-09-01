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
            'place_of_birth' => $parameters->get('place_of_birth'),
            'phone_number' => $parameters->get('phone_number'),
        ]);

        $club = $parameters->get('club');
        $club_id = null;
        $club_name = null;
        if (!is_null($club)) {
            $club_id = array_key_exists('id', $club) ? $club['id'] : null;
            $club_name = array_key_exists('name', $club) ? $club['name'] : null;
        }
        if (!$club_id && !is_null($club_name)) {
            $new_club = new ArcheryClub();
            $new_club->name = $club_name;
            $new_club->save();
            $club_id = $new_club->id;
        }

        $user_archery_info = new UserArcheryInfo();
        $user_archery_info->user_id = $user->id;
        $user_archery_info->archery_club_id = $club_id;
        $user_archery_info->save();

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
        ];
    }
}
