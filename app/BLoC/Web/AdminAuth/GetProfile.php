<?php

namespace App\BLoC\Web\AdminAuth;

use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;

class GetProfile extends Retrieval
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
