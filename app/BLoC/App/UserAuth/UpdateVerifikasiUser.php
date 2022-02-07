<?php

namespace App\BLoC\App\UserAuth;

use App\Libraries\Upload;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class UpdateVerifikasiUser extends Retrieval
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

        if (!$user) {
            throw new BLoCException("user not found");
        }

        if ($user_login->id !== $user->id) {
            throw new BLoCException("forbiden");
        }

        if ($user->verify_status == 4 || $user->verify_status == 3 || $user->verify_status == 2) {
            if ($parameters->get('ktp')) {
                $ktp = Upload::setPath("asset/ktp/")->setFileName("ktp_" . $user->id)->setBase64($parameters->get('ktp'))->save();
                $user->ktp = $ktp;
            }

            if ($parameters->get('kk')) {
                $kk = Upload::setPath("asset/kk/")->setFileName("kk_" . $user->id)->setBase64($parameters->get('kk'))->save();
                $user->kk = $kk;
            }

            if ($parameters->get('name')) {
                $user->name = $parameters->get('name');
            }

            $user->nik = $parameters->get('nik');

            $user->verify_status = 3;

            $user->save();
        }else{
            throw new BLoCException("this user already verified");
        }

        return $user;
    }

    protected function validation($parameters)
    {
        return [
            "user_id" => 'required|integer',
            "kk" => 'required|string',
            "ktp" => 'required|string',
            "nik" => 'required|string|min:16|max:16'
        ];
    }
}
