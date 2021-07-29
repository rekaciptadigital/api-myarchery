<?php

namespace App\BLoC\Web\Permission;

use App\Models\Permission;
use DAI\Utils\Abstracts\Transactional;

class EditPermission extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $permission = Permission::find($parameters->get('id'));
        $permission->key = $parameters->get('key');
        $permission->description = $parameters->get('description');
        $permission->always_allow = $parameters->get('always_allow');
        $permission->label = $parameters->get('label');
        $permission->save();

        return $permission;
    }

    protected function validation($parameters)
    {
        return [
            'id' => [
                'required',
                'exists:permissions,id',
            ],
            'key' => "required|unique:permissions,key,{$parameters->get('id')}",
            'label' => 'required',
            'description' => 'required',
            'always_allow' => 'required',
        ];
    }
}
