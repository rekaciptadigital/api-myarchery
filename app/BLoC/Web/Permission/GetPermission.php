<?php

namespace App\BLoC\Web\Permission;

use App\Models\Permission;
use DAI\Utils\Abstracts\Retrieval;

class GetPermission extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $permissions = Permission::all();

        return $permissions;
    }
}
