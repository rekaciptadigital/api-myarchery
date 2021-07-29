<?php

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        DB::beginTransaction();
        try {
            // generate for tables
            Permission::generateFor('users');
            Permission::generateFor('roles');
            Permission::generateFor('permissions');
            Permission::generateFor('user_roles');
            Permission::generateFor('role_permissions');
            Permission::generateFor('menus');
            Permission::generateFor('menu_items');
            Permission::generateFor('menu_item_permissions');
            Permission::generateFor('user_archery_info');
            Permission::generateFor('archery_age_categories');
            Permission::generateFor('archery_categories');
            Permission::generateFor('archery_clubs');

            // custom permission
            $keys = [];

            foreach ($keys as $key) {
                Permission::firstOrCreate([
                    'key' => $key
                ]);
            }
            DB::commit();
        } catch (Exception $e) {
            throw new Exception('Exception occur ' . $e);
            DB::rollBack();
        }
    }
}
