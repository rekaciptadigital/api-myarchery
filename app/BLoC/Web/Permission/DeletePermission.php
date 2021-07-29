<?php

namespace App\BLoC\Web\Permission;

use App\Models\Permission;
use DAI\Utils\Abstracts\Transactional;

class DeletePermission extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        Permission::find($parameters->get('id'))->delete();

        return [];
    }

    protected function validation($parameters)
    {
        return [
            'id' => [
                'required',
                'exists:permissions',
            ],
        ];
    }
}
