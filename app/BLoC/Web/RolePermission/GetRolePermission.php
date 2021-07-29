<?php

namespace App\BLoC\Web\RolePermission;

use App\Models\RolePermission;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Abstracts\Retrieval;

class GetRolePermission extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $role_permissions = RolePermission::where('role_id', $parameters->get('role_id'))->get();

        return $role_permissions;
    }

    protected function validation($parameters)
    {
        return [
            'role_id' => 'required|exists:roles,id',
        ];
    }
}
