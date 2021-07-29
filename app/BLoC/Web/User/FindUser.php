<?php

namespace App\BLoC\Web\User;

use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;

class FindUser extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = User::find($parameters->get('id'));

        return $user;
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|exists:users,id',
        ];
    }
}