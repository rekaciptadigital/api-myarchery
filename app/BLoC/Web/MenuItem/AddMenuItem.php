<?php

namespace App\BLoC\Web\MenuItem;

use App\Models\MenuItem;
use DAI\Utils\Abstracts\Transactional;

class AddMenuItem extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $new_menu_item = new MenuItem();
        $new_menu_item->menu_id = $parameters->get('menu_id');
        $new_menu_item->title = $parameters->get('title');
        $new_menu_item->url = substr($parameters->get('url'), 0, 1) != '/' ? '/' . $parameters->get('url') : $parameters->get('url');
        $new_menu_item->target = $parameters->get('target') ? $parameters->get('target') : '_self';
        $new_menu_item->icon_class = $parameters->get('icon_class');
        $new_menu_item->color = $parameters->get('color');
        $new_menu_item->parent_id = $parameters->get('parent_id');
        $new_menu_item->order = $new_menu_item->highestOrderMenuItem();
        $new_menu_item->save();

        return $new_menu_item;
    }

    protected function validation($parameters)
    {
        return [
            'menu_id' => ['required', 'exists:menus,id'],
            'title' => ['required'],
            'url' => ['required'],
            'target' => ['required'],
        ];
    }
}
