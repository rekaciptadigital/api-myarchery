<?php

namespace App\BLoC\Web\AdminRole;

use App\Models\AdminRole;
use DAI\Utils\Abstracts\Retrieval;

class GetAllAdminRole extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin_roles = AdminRole::all();

        return $admin_roles;
    }
}
