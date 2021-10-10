<?php

namespace App\BLoC\Web\AdminAuth;

use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

class GetProfile extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        return Admin::getProfile();
    }

    protected function validation($parameters)
    {
        return [];
    }
}
