<?php

namespace App\BLoC\App\UserAuth;

use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;

class GetDataUserVerifikasi extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = User::find($parameters->get('user_id'));
        $output = [
            "user_id" => $user->id,
            "nik" => $user->nik,
            "ktp_kk" => $user->ktp_kk,
            "selfie_ktp_kk" => $user->selfie_ktp_kk
        ];

        return $output;
    }

    protected function validation($parameters)
    {
        return [
            'user_id' => 'required|integer'
        ];
    }
}
