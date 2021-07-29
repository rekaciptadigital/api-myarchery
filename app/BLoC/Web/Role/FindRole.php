<?php

namespace App\BLoC\Web\Role;

use App\Models\Role;
use DAI\Utils\Abstracts\Retrieval;

class FindRole extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $role = Role::find($parameters->get('id'));

        return $role;
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|exists:roles,id',
        ];
    }
}