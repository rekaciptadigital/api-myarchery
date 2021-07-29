<?php

namespace App\BLoC\Web\MenuItem;

use DAI\Utils\Abstracts\Transactional;
use App\Models\MenuItem;

class EditMenuItem extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $menu_item = MenuItem::find($parameters->get('menu_item_id'));
        $menu_item->menu_id = $parameters->get('menu_id');
        $menu_item->title = $parameters->get('title');
        $menu_item->url = substr($parameters->get('url'), 0, 1) != '/' ? '/' . $parameters->get('url') : $parameters->get('url');
        $menu_item->target = $parameters->get('target') ? $parameters->get('target') : '_self';
        $menu_item->icon_class = $parameters->get('icon_class');
        $menu_item->color = $parameters->get('color');
        $menu_item->save();

        return $menu_item;
    }

    protected function validation($parameters)
    {
        return [
            'menu_id' => ['required', 'exists:menus,id'],
            'menu_item_id' => ['required', 'exists:menu_items,id'],
            'title' => ['required'],
            'url' => ['required'],
            'target' => ['required'],
        ];
    }
}
