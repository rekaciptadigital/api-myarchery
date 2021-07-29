<?php

namespace App\BLoC\Web\Permission;

use App\Models\Permission;
use DAI\Utils\Abstracts\Transactional;

class BulkDeletePermission extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $id_list = $parameters->get('ids');

        foreach ($id_list as $key => $id) {
            Permission::find($id)->delete();
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
