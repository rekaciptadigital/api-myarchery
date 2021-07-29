<?php

namespace App\BLoC\Web\Role;

use App\Models\Role;
use DAI\Utils\Abstracts\Transactional;

class AddRole extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $role = new Role();
        $role->name = $parameters->get('name');
        $role->display_name = $parameters->get('display_name');
        $role->description = $parameters->get('description');
        $role->save();

        return $role;
    }

    protected function validation($parameters)
    {
        return [
            'name' => 'required|unique:roles,name',
            'display_name' => 'required',
            'description' => 'required',
        ];
    }
}
