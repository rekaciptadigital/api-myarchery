<?php

namespace App\BLoC\Web\Admin;

use App\Models\Admin;
use DAI\Utils\Abstracts\Transactional;

class BulkDeleteAdmin extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $id_list = $parameters->get('ids');

        foreach ($id_list as $key => $id) {
            $admin = Admin::find($id);
            if (!is_null($admin)) {
                $this->deleteFile($admin->avatar);
                $admin->delete();
            }
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
