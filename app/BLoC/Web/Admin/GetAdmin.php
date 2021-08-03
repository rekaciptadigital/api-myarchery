<?php

namespace App\BLoC\Web\Admin;

use App\Models\Admin;
use DAI\Utils\Abstracts\Retrieval;

class GetAdmin extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admins = Admin::all();

        return $admins;
    }
}
