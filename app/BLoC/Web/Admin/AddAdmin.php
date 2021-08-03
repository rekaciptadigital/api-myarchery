<?php

namespace App\BLoC\Web\Admin;

use App\Models\Admin;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Rules\Base64;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AddAdmin extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = new Admin();
        $admin->name = $parameters->get('name');
        $admin->email = $parameters->get('email');
        $uploaded = null;
        $avatar = $parameters->get('avatar');
        if ($avatar && $avatar != '') {
            $extension = explode('/', explode(';', $avatar)[0])[1];
            $uploaded = $this->saveBase64($avatar, Str::slug($parameters->get('name')) . '.' . $extension, 'users');
        }
        $admin->avatar = $uploaded;
        $admin->password = Hash::make($parameters->get('password'));
        $admin->save();

        return $admin;
    }

    protected function validation($parameters)
    {
        return [
            'email' => 'required|email|unique:admins',
            'name' => 'required',
            'avatar' => [
                new Base64()
            ],
        ];
    }
}
