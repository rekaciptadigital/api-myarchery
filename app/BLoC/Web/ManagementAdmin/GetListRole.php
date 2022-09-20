<?php

namespace App\BLoC\Web\ManagementAdmin;

use App\Models\Role;
use DAI\Utils\Abstracts\Retrieval;

class GetListRole extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $roles = Role::whereIn("id", [6])->get();
        return $roles;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
