<?php

namespace App\BLoC\Web\Role;

use App\Models\Role;
use DAI\Utils\Abstracts\Transactional;

class DeleteRole extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        Role::find($parameters->get('id'))->delete();

        return [];
    }

    protected function validation($parameters)
    {
        return [
            'id' => [
                'required',
                'exists:roles',
            ],
        ];
    }
}
