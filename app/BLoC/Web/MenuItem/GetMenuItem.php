<?php

namespace App\BLoC\Web\MenuItem;

use App\Models\MenuItem;
use DAI\Utils\Abstracts\Retrieval;

class GetMenuItem extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $menu_items = MenuItem::where('menu_id', $parameters->get('menu_id'))
            ->orderBy('order', 'asc')
            ->whereNull('menu_items.parent_id')
            ->get();

        $menu_items = $this->getChildMenuItems($menu_items);

        return $menu_items;
    }

    protected function validation($parameters)
    {
        return [
            'menu_id' => ['required'],
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
