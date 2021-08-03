<?php

namespace App\BLoC\Web\AdminRole;

use App\Models\AdminRole;
use DAI\Utils\Abstracts\Retrieval;

class GetAdminRole extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin_roles = AdminRole::where('admin_id', $parameters->get('admin_id'))->get();

        return $admin_roles;
    }

    protected function validation($parameters)
    {
        return [
            'admin_id' => 'required|exists:admins,id',
        ];
    }
}
