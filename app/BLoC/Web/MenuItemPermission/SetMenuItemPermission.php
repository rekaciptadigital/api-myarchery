<?php

namespace App\BLoC\Web\MenuItemPermission;

use App\Models\MenuItem;
use App\Models\MenuItemPermission;
use App\Models\Permission;
use DAI\Utils\Abstracts\Transactional;

class SetMenuItemPermission extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $permission_ids = $parameters->get('permissions', []);

        MenuItemPermission::where('menu_item_id', $parameters->get('menu_item_id'))->delete();

        $menu_item_permissions = [];
        foreach ($permission_ids as $key => $value) {
            $menu_item_permission = new MenuItemPermission();
            $menu_item_permission->menu_item_id = $parameters->get('menu_item_id');
            $menu_item_permission->permission_id = $value;
            $menu_item_permission->save();
            $menu_item_permissions[] = $menu_item_permission;
        }

        return $menu_item_permissions;
    }

    protected function validation($parameters)
    {
        return [
            'menu_id' => ['required', 'exists:menus,id'],
            'menu_item_id' => ['required', 'exists:menu_items,id'],
            'permissions.*' => ['required', 'exists:permissions,id']
        ];
    }
}
