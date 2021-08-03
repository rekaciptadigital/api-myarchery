<?php

namespace App\BLoC\Web\Admin;

use App\Models\Admin;
use DAI\Utils\Abstracts\Transactional;

class DeleteAdmin extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {

        $admin = Admin::find($parameters->get('id'));
        $this->deleteFile($admin->get('avatar'));
        $admin->delete();

        return [];
    }

    protected function validation($parameters)
    {
        return [
            'id' => [
                'required',
                'exists:admins',
            ],
        ];
    }
}
