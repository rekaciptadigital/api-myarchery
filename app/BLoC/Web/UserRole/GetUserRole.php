<?php

namespace App\BLoC\Web\UserRole;

use App\Models\UserRole;
use DAI\Utils\Abstracts\Retrieval;

class GetUserRole extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user_roles = UserRole::where('user_id', $parameters->get('user_id'))->get();

        return $user_roles;
    }

    protected function validation($parameters)
    {
        return [
            'user_id' => 'required|exists:users,id',
        ];
    }
}
