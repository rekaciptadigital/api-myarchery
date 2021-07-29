<?php

namespace App\BLoC\Web\RolePermission;

use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;

class GetAllPermissionWithRole extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $query = '
                SELECT A.*,
                    CASE
                        WHEN B.role_id is not null then 1
                        else 0
                    END as selected
                FROM permissions A
                LEFT JOIN role_permissions B ON A.id = B.permission_id AND B.role_id = :role_id
            ';
        $role_permissions = DB::select($query, [
            'role_id' => $parameters->get('role_id'),
        ]);

        return $role_permissions;
    }

    protected function validation($parameters)
    {
        return [
            'role_id' => 'required|exists:roles,id',
        ];
    }
}
