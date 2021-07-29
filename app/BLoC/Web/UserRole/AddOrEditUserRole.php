<?php

namespace App\BLoC\Web\UserRole;

use App\Models\User;
use App\Models\UserRole;
use DAI\Utils\Abstracts\Transactional;

class AddOrEditUserRole extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $roles = $parameters->get('roles');

        $user = User::find($parameters->get('user_id'));
        $stored_roles = [];
        if (!is_null($user)) {
            foreach ($roles as $key => $value) {
                $user_role = [
                    'user_id' => $user->id,
                    'role_id' => $value,
                ];

                $stored_role = UserRole::firstOrCreate($user_role);
                $stored_roles[] = $stored_role;
            }

            $deleted_items = UserRole::where('user_id', $user->id)->whereNotIn('role_id', $roles)->get();
            foreach ($deleted_items as $item) {
                UserRole::find($item->id)->delete();
            }
        }

        return $stored_role;
    }

    protected function validation($parameters)
    {
        return [
            'user_id' => 'required|exists:users,id',
            'roles.*' => ['required', 'exists:roles,id']
        ];
    }
}
