<?php

namespace App\BLoC\Web\AdminAuth;

use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminLoginToken;

class Logout extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $private_signature = Auth::payload()["jti"];
        $admin = Auth::user();
        Auth::logout();
        AdminLoginToken::where("admin_id",$admin->id)->where("private_signature",$private_signature)->delete();
    }

    protected function validation($parameters)
    {
        return [];
    }
}
