<?php

namespace App\BLoC\App\UserAuth;

use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use App\Models\UserLoginToken;

class UserLogout extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $private_signature = Auth::payload()["jti"];
        $user = Auth::guard('app-api')->user();
        UserLoginToken::where("user_id",$user->id)->where("private_signature",$private_signature)->delete();

        Auth::guard('app-api')->logout();
    }

    protected function validation($parameters)
    {
        return [];
    }
}
