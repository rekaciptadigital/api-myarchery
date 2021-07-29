<?php

namespace App\BLoC\Web\UserRole;

use App\Models\UserRole;
use DAI\Utils\Abstracts\Retrieval;

class GetAllUserRole extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user_roles = UserRole::all();

        return $user_roles;
    }
}
