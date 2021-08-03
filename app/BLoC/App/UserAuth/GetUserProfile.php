<?php

namespace App\BLoC\App\UserAuth;

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
        return Auth::guard('app-api')->user();
    }

    protected function validation($parameters)
    {
        return [];
    }
}
