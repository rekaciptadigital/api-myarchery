<?php

namespace App\BLoC\App\UserAuth;

use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
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
        if (!$user) {
            throw new BLoCException("user not found");
        }

        return $user->getDataVerifikasiUser();
    }

    protected function validation($parameters)
    {
        return [
            'user_id' => 'required|integer'
        ];
    }
}
