<?php

namespace App\BLoC\Web\MenuItem;

use App\Models\MenuItem;
use DAI\Utils\Abstracts\Retrieval;

class FindMenuItem extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $menu_item = MenuItem::where('menu_id', $parameters->get('menu_id'))
            ->where('id', $parameters->get('menu_item_id'))
            ->first();

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
