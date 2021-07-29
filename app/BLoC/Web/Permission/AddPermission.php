<?php

namespace App\BLoC\Web\Permission;

use App\Models\Permission;
use DAI\Utils\Abstracts\Transactional;

class AddPermission extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $permission = new Permission();
        $permission->key = $parameters->get('key');
        $permission->label = $parameters->get('label');
        $permission->description = $parameters->get('description');
        $permission->always_allow = $parameters->get('always_allow');
        $permission->save();

        return $permission;
    }

    protected function validation($parameters)
    {
        return [
            'key' => 'required|unique:permissions',
            'label' => 'required',
            'description' => 'required',
            'always_allow' => 'required',
        ];
    }
}