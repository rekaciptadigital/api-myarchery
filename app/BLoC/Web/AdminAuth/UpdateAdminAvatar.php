<?php

namespace App\BLoC\Web\AdminAuth;

use App\Libraries\Upload;
use App\Models\Admin;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class UpdateAdminAvatar extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin_login = Auth::user();
        $admin = Admin::find($admin_login->id);

        if(!$admin){
            throw new BLoCException("admin not found");
        }

    
        if($parameters->get('avatar')){
            $avatar = Upload::setPath("asset/admin_avatar/")->setFileName("avatar_" . $admin->id)->setBase64($parameters->get('avatar'))->save();
            $admin->avatar = $avatar;
            $admin->save();
        }

        return $admin->avatar;
    }

    protected function validation($parameters)
    {
        return [
            "avatar" => 'required|string'
        ];
    }
}
