<?php

namespace App\BLoC\Web\MenuItem;

use DAI\Utils\Abstracts\Transactional;
use App\Models\MenuItem;

class BulkUpdateMenuItemOrder extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $this->updateMenuItems($parameters->get('menu_items'));

        return [];
    }

    protected function validation($parameters)
    {
        return [
            'menu_id' => ['required', 'exists:menus,id'],
            'menu_items' => ['required'],
        ];
    }

    private function updateMenuItems($items, $parent_id = null)
    {
        foreach ($items as $index => $item) {
            $menu_item = MenuItem::find($item['id']);
            $menu_item->order = $index + 1;
            $menu_item->parent_id = $parent_id;
            $menu_item->save();
            if (array_key_exists('children', $item) && count($item['children']) > 0) {
                $this->updateMenuItems($item['children'], $item['id']);
            }
        }
    }
}
