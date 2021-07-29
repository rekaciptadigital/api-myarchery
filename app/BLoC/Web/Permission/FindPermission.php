<?php

namespace App\BLoC\Web\Permission;

use App\Models\Permission;
use DAI\Utils\Abstracts\Retrieval;

class FindPermission extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $permission = Permission::find($parameters->get('id'));

        return $permission;
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|exists:permissions,id',
        ];
    }
}