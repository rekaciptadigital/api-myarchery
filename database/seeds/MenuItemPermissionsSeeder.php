<?php

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuItemPermissionsSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        DB::beginTransaction();
        try {
            $permissions = Permission::all();
            $permissions = collect($permissions);

            $menu_item_permissions = [
                0 => [
                    'menu_item_id' => 1,
                    'permission_id' => $this->getPermissionId($permissions, 'browse_permissions')
                ],
                1 => [
                    'menu_item_id' => 2,
                    'permission_id' => $this->getPermissionId($permissions, 'browse_roles')
                ],
                2 => [
                    'menu_item_id' => 3,
                    'permission_id' => $this->getPermissionId($permissions, 'browse_users')
                ],
                3 => [
                    'menu_item_id' => 4,
                    'permission_id' => $this->getPermissionId($permissions, 'browse_menus')
                ],
            ];

            $new_menu_item_permissions = [];
            foreach ($menu_item_permissions as $key => $value) {
                $menu_item_permission = DB::table('menu_item_permissions')
                    ->where('menu_item_id', $value['menu_item_id'])
                    ->where('permission_id', $value['permission_id'])
                    ->first();

                if (is_null($value['permission_id']) || isset($menu_item_permission)) {
                    continue;
                }
                $value['id'] = $key + 1;
                $value['created_at'] = '2021-01-01 15:26:06';
                $value['updated_at'] = '2021-01-01 15:26:06';
                $new_menu_item_permissions[] = $value;
            }

            DB::table('menu_item_permissions')->insert($new_menu_item_permissions);
            DB::commit();
        } catch (Exception $e) {
            throw new Exception('Exception occur ' . $e);
            DB::rollBack();
        }
    }

    private function getPermissionId($permissions, $key)
    {
        $permission = $permissions->firstWhere('key', $key);
        if (isset($permission)) {
            return $permission->id;
        }
        return null;
    }
}
