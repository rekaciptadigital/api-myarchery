<?php

namespace App\BLoC\Web\User;

use App\Models\User;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Rules\Base64;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AddUser extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = new User();
        $user->name = $parameters->get('name');
        $user->email = $parameters->get('email');
        $uploaded = null;
        $avatar = $parameters->get('avatar');
        if ($avatar && $avatar != '') {
            $extension = explode('/', explode(';', $avatar)[0])[1];
            $uploaded = $this->saveBase64($avatar, Str::slug($parameters->get('name')) . '.' . $extension, 'users');
        }
        $user->avatar = $uploaded;
        $user->password = Hash::make($parameters->get('password'));
        $user->save();

        return $user;
    }

    protected function validation($parameters)
    {
        return [
            'email' => 'required|email|unique:users',
            'name' => 'required',
            'avatar' => [
                new Base64()
            ],
        ];
    }
}
