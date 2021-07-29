<?php

namespace App\BLoC\Web\MenuItem;

use DAI\Utils\Abstracts\Transactional;
use App\Models\MenuItem;

class EditMenuItemOrder extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $menu_item = MenuItem::find($parameters->get('menu_item_id'));
        $order = $parameters->get('order');

        $old_order = $menu_item('order');
        $new_order = $order;

        if (is_null($old_order)) {
            $old_order = 0;
        }

        if ($new_order > $old_order) {
            $menu_items = MenuItem::where('order', '<=', $new_order)
                ->where('order', '>', $old_order)
                ->where('menu_id', $parameters->get('menu_id'))
                ->get();
            foreach ($menu_items as $item) {
                $other_menu_item = MenuItem::find($item->id);
                $other_menu_item->order = $other_menu_item->order - 1;
                $other_menu_item->save();
            }
        } else {
            $menu_items = MenuItem::where('order', '>=', $new_order)
                ->where('order', '<', $old_order)
                ->where('menu_id', $parameters->get('menu_id'))
                ->get();
            foreach ($menu_items as $item) {
                $other_menu_item = MenuItem::find($item->id);
                $other_menu_item->order = $other_menu_item->order + 1;
                $other_menu_item->save();
            }
        }

        $menu_item->order = $order;
        $menu_item->save();

        return $menu_item;
    }

    protected function validation($parameters)
    {
        return [
            'menu_id' => ['required', 'exists:menus,id'],
            'menu_item_id' => ['required', 'exists:menu_items,id'],
        ];
    }
}
