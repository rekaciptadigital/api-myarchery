<?php

namespace App\BLoC\Web\User;

use App\Models\User;
use DAI\Utils\Abstracts\Transactional;

class BulkDeleteUser extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $id_list = $parameters->get('ids');

        foreach ($id_list as $key => $id) {
            $user = User::find($id);
            if (!is_null($user)) {
                $this->deleteFile($user->avatar);
                $user->delete();
            }
        }

        return [];
    }

    protected function validation($parameters)
    {
        return [
            'ids' => [
                'required',
                'array'
            ],
        ];
    }
}
