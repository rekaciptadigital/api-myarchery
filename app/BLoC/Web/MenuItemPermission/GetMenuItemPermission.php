<?php

namespace App\BLoC\Web\MenuItemPermission;

use App\Models\MenuItem;
use App\Models\MenuItemPermission;
use App\Models\Permission;
use DAI\Utils\Abstracts\Retrieval;

class GetMenuItemPermission extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $menu_item = MenuItem::find($parameters->get('menu_item_id'));
        $menu_item_permissions = MenuItemPermission::get($parameters->get('menu_item_id'));
        $menu_item_permissions = collect($menu_item_permissions)->pluck('permission_id')->toArray();

        if ($menu_item) {
            $menu_item_permissions = explode(',', $menu_item->permissions);
            $permissions = Permission::all();
            $custom_permissions = [];
            foreach ($permissions as $index => $permission) {
                if (in_array($permission->key, $menu_item_permissions)) {
                    $permission->selected = true;
                } else {
                    $permission->selected = false;
                }
                $custom_permissions[] = $permission;
            }
            $menu_item->menu_item_permissions = $custom_permissions;
        }

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
