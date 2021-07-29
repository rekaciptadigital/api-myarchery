<?php

namespace App\BLoC\Web\Menu;

use App\Models\Menu;
use DAI\Utils\Abstracts\Retrieval;

class GetMenu extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $menus = Menu::all();

        return $menus;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
