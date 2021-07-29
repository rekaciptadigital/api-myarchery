<?php

namespace App\BLoC\Web\MenuItem;

use App\Models\Menu;
use App\Models\MenuItem;
use DAI\Utils\Abstracts\Retrieval;

class GetMenuItemByKey extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $menu = Menu::where('key', $parameters->get('menu_key'))->first();

        $all_menu_items = MenuItem::join('menus', 'menus.id', 'menu_items.menu_id')
            ->where('menus.key', $parameters->get('menu_key'))
            ->whereNull('menu_items.parent_id')
            ->select('menu_items.*')
            ->orderBy('menu_items.order', 'asc')
            ->get();
        $menu_items = [];
        foreach ($all_menu_items as $menu_item) {
            $allowed = true; //AuthenticatedUser::isAllowedTo($menu_item->permissions);
            if ($allowed) {
                $menu_items[] = $menu_item;
            }
        }
        $menu_items = $this->getChildMenuItems($menu_items);

        $menu->menu_items = $menu_items;

        return $menu;
    }

    protected function validation($parameters)
    {
        return [
            'menu_key' => ['required', 'exists:menus,key'],
        ];
    }

    private function getChildMenuItems($menu_items)
    {
        $new_menu_items = $menu_items;
        foreach ($new_menu_items as $key => $value) {
            if ($value->hasChildren()) {
                $all_childrens = MenuItem::where('parent_id', $value->id)
                    ->orderBy('order', 'asc')
                    ->get();
                $childrens = [];
                foreach ($all_childrens as $children) {
                    $allowed = true; // AuthenticatedUser::isAllowedTo($children->permissions);
                    if ($allowed) {
                        $childrens[] = $children;
                    }
                }
                $children = $this->getChildMenuItems($childrens);
                $value['children'] = collect($children)->toArray();
            }
        }

        return $new_menu_items;
    }
}
