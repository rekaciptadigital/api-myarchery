<?php

namespace App\BLoC\Web\UserRole;

use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;

class GetAllRoleWithUser extends Retrieval
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
                        WHEN B.user_id is not null then 1
                        else 0
                    END as selected
                FROM roles A
                LEFT JOIN user_roles B ON A.id = B.role_id AND B.user_id = :user_id
            ';
        $user_roles = DB::select($query, [
            'user_id' => $parameters->get('user_id'),
        ]);

        return $user_roles;
    }

    protected function validation($parameters)
    {
        return [
            'user_id' => 'required|exists:users,id',
        ];
    }
}
