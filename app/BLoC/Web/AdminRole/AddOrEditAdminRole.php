<?php

namespace App\BLoC\Web\AdminRole;

use App\Models\Admin;
use App\Models\AdminRole;
use DAI\Utils\Abstracts\Transactional;

class AddOrEditAdminRole extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $roles = $parameters->get('roles');

        $admin = Admin::find($parameters->get('admin_id'));
        $stored_roles = [];
        if (!is_null($admin)) {
            foreach ($roles as $key => $value) {
                $admin_role = [
                    'admin_id' => $admin->id,
                    'role_id' => $value,
                ];

                $stored_role = AdminRole::firstOrCreate($admin_role);
                $stored_roles[] = $stored_role;
            }

            $deleted_items = AdminRole::where('admin_id', $admin->id)->whereNotIn('role_id', $roles)->get();
            foreach ($deleted_items as $item) {
                AdminRole::find($item->id)->delete();
            }
        }

        return $stored_role;
    }

    protected function validation($parameters)
    {
        return [
            'admin_id' => 'required|exists:admins,id',
            'roles.*' => ['required', 'exists:roles,id']
        ];
    }
}
