<?php

namespace App\BLoC\Web\Admin;

use App\Models\Admin;
use DAI\Utils\Abstracts\Retrieval;

class FindAdmin extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Admin::find($parameters->get('id'));

        return $admin;
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|exists:admins,id',
        ];
    }
}