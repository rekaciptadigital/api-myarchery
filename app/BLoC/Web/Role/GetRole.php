<?php

namespace App\BLoC\Web\Role;

use App\Models\Role;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Abstracts\Retrieval;

class GetRole extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $roles = Role::all();

        return $roles;
    }
}
