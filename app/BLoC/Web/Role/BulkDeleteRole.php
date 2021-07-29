<?php

namespace App\BLoC\Web\Role;

use App\Models\Role;
use DAI\Utils\Abstracts\Transactional;

class BulkDeleteRole extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $id_list = $parameters->get('ids');

        foreach ($id_list as $key => $id) {
            Role::find($id)->delete();
        }

        return [];
    }

    protected function validation($parameters)
    {
        return [
            'ids' => [
                'required',
                'array'
            ],
        ];
    }
}