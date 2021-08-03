<?php

namespace App\BLoC\Web\Menu;

use App\Models\Menu;
use DAI\Utils\Abstracts\Transactional;

class EditMenu extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $menu = Menu::find($parameters->get('id'));
        $menu->key = $parameters->get('key');
        $menu->display_name = $parameters->get('display_name');
        $menu->icon = $parameters->get('icon');
        $menu->save();

        return $menu;
    }

    protected function validation($parameters)
    {
        $id = $parameters->get('id');
        return [
            'id' => ['required', 'exists:menus,id'],
            'key' => ['required', "unique:menus,id,$id"],
            'display_name' => ['required'],
        ];
    }
}
