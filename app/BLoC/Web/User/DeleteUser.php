<?php

namespace App\BLoC\Web\User;

use App\Models\User;
use DAI\Utils\Abstracts\Transactional;

class DeleteUser extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {

        $user = User::find($parameters->get('id'));
        $this->deleteFile($user->get('avatar'));
        $user->delete();

        return [];
    }

    protected function validation($parameters)
    {
        return [
            'id' => [
                'required',
                'exists:users',
            ],
        ];
    }
}
