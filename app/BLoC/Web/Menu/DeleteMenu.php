<?php

namespace App\BLoC\Web\Menu;

use App\Models\Menu;
use DAI\Utils\Abstracts\Transactional;

class DeleteMenu extends Transactional
{
    public function getDescription()
    {
        return "";
    }


    protected function process($parameters)
    {
        Menu::find($parameters->get('id'))->delete();

        return [];
    }

    protected function validation($parameters)
    {
        return [
            'id' => ['required', 'exists:menus,id'],
        ];
    }
}
