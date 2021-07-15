<?php

namespace App\BLoC\General;

use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;

class Logout extends Transactional
{
    public function getDescription()
    {
    }

    protected function prepare($params, $original_params)
    {
        return $params;
    }

    protected function process($params, $original_params)
    {
        Auth::logout();
    }

    protected function rules()
    {
        return [];
    }
}
