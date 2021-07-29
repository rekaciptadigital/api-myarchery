<?php

namespace App\BLoC\Web\User;

use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;

class GetUser extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $users = User::all();

        return $users;
    }
}
