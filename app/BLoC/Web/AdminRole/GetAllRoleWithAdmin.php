<?php

namespace App\BLoC\Web\AdminRole;

use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;

class GetAllRoleWithAdmin extends Retrieval
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
                        WHEN B.admin_id is not null then 1
                        else 0
                    END as selected
                FROM roles A
                LEFT JOIN admin_roles B ON A.id = B.role_id AND B.admin_id = :admin_id
            ';
        $admin_roles = DB::select($query, [
            'admin_id' => $parameters->get('admin_id'),
        ]);

        return $admin_roles;
    }

    protected function validation($parameters)
    {
        return [
            'admin_id' => 'required|exists:admins,id',
        ];
    }
}
