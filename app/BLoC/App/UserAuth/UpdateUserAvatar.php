<?php

namespace App\BLoC\App\UserAuth;

use App\Libraries\Upload;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class UpdateUserAvatar extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user_login = Auth::guard('app-api')->user();

        $user_id = $parameters->get('user_id');
        $user = User::find($user_id);

        if(!$user){
            throw new BLoCException("user not found");
        }

        if($user_login->id !== $user->id){
            throw new BLoCException("forbiden");
        }

        if($parameters->get('avatar')){
            $avatar = Upload::setPath("asset/avatar/")->setFileName("avatar_" . $user->id)->setBase64($parameters->get('avatar'))->save();
            $user->avatar = $avatar;
            $user->save();
        }

        return $user->avatar;
    }

    protected function validation($parameters)
    {
        return [
            "user_id" => 'required|integer',
            "avatar" => 'required|string'
        ];
    }
}
