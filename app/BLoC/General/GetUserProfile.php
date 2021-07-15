<?php

namespace App\BLoC\General;

use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;

class GetUserProfile extends Retrieval
{
    public function getDescription()
    {
    }

    protected function process($params)
    {
        return Auth::user();
    }

    protected function rules()
    {
        return [];
    }
}
