<?php

namespace App\BLoC\Web\RolePermission;

use App\Models\RolePermission;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Abstracts\Retrieval;

class GetAllRolePermission extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $role_permissions = RolePermission::all();

        return $role_permissions;
    }
}