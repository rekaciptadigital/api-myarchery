<?php

namespace App\BLoC\Web\MenuItem;

use App\Models\MenuItem;
use DAI\Utils\Abstracts\Transactional;

class DeleteMenuItem extends Transactional
{
    public function getDescription()
    {
        return "";
    }


    protected function process($parameters)
    {
        MenuItem::find($parameters->get('menu_item_id'))->delete();

        return [];
    }

    protected function validation($parameters)
    {
        return [
            'menu_id' => ['required', 'exists:menus,id'],
            'menu_item_id' => ['required', 'exists:menu_items,id'],
        ];
    }
}
