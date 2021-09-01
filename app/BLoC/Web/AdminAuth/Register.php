<?php

namespace App\BLoC\Web\AdminAuth;

use App\Models\Admin;
use App\Models\AdminRole;
use App\Models\Role;
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
        $admin = Admin::create([
            'name' => $parameters->get('name'),
            'email' => $parameters->get('email'),
            'password' => Hash::make($parameters->get('password')),
            'date_of_birth' => $parameters->get('date_of_birth'),
            'place_of_birth' => $parameters->get('place_of_birth'),
            'phone_number' => $parameters->get('phone_number'),
        ]);

        $role = Role::where('name', 'event_organizer')->first();

        $admin_role = new AdminRole();
        $admin_role->admin_id = $admin->id;
        $admin_role->role_id = !is_null($role) ? $role->id : null;
        $admin_role->save();

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
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:6|confirmed',
        ];
    }
}
