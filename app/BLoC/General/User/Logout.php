<?php

namespace App\BLoC\General\User;

use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;

class Logout extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        Auth::logout();
    }

    protected function validation($parameters)
    {
        return [];
    }
}
