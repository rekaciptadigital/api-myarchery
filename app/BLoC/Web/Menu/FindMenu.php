<?php

namespace App\BLoC\Web\Menu;

use App\Models\Menu;
use DAI\Utils\Abstracts\Retrieval;

class FindMenu extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $menu = Menu::find($parameters->get('id'));

        return $menu;
    }

    protected function validation($parameters)
    {
        return [
            'id' => ['required', 'exists:menus,id'],
        ];
    }
}
