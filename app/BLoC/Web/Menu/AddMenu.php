<?php

namespace App\BLoC\Web\Menu;

use App\Models\Menu;
use DAI\Utils\Abstracts\Transactional;

class AddMenu extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $new_menu = new Menu();
        $new_menu->key = $parameters->get('key');
        $new_menu->display_name = $parameters->get('display_name');
        $new_menu->icon = $parameters->get('icon');
        $new_menu->save();

        return $new_menu;
    }

    protected function validation($parameters)
    {
        return [
            'key' => ['required', 'unique:menus'],
            'display_name' => ['required'],
        ];
    }
}
