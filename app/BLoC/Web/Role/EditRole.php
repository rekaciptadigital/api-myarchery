<?php

namespace App\BLoC\Web\Role;

use App\Models\Role;
use DAI\Utils\Abstracts\Transactional;

class EditRole extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $role = Role::find($parameters->get('id'));
        $role->name = $parameters->get('name');
        $role->display_name = $parameters->get('display_name');
        $role->description = $parameters->get('description');
        $role->save();

        return $role;
    }

    protected function validation($parameters)
    {
        return [
            'id' => [
                'required',
                'exists:roles,id',
            ],
            'name' => "required|unique:roles,name,{$parameters->get('id')}",
            'display_name' => 'required',
            'description' => 'required',
        ];
    }
}
