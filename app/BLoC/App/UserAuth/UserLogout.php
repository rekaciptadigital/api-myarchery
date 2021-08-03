<?php

namespace App\BLoC\App\UserAuth;

use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;

class UserLogout extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        Auth::guard('app-api')->logout();
    }

    protected function validation($parameters)
    {
        return [];
    }
}
