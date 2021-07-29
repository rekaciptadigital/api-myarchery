<?php

namespace App\BLoC\General\User;

use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;

class GetUserProfile extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        return Auth::user();
    }

    protected function validation($parameters)
    {
        return [];
    }
}
