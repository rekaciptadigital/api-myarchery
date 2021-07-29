<?php

namespace App\BLoC\Web\RolePermission;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Abstracts\Retrieval;

class AddOrEditRolePermission extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $permissions = $parameters->get('permissions');

        $role = Role::find($parameters->get('role_id'));
        $stored_permissions = [];
        if (!is_null($role)) {
            foreach ($permissions as $key => $value) {
                $role_permission = [
                    'role_id' => $role->id,
                    'permission_id' => $value,
                ];

                $stored_permission = RolePermission::firstOrCreate($role_permission);
                $stored_permissions[] = $stored_permission;
            }

            $deleted_items = RolePermission::where('role_id', $role->id)->whereNotIn('permission_id', $permissions)->get();

            foreach ($deleted_items as $item) {
                RolePermission::find($item->id)->delete();
            }
        }

        return $stored_permissions;
    }

    protected function validation($parameters)
    {
        return [
            'role_id' => 'required|exists:roles,id',
            'permissions.*' => ['required', 'exists:permissions,id']
        ];
    }
}
