<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuItemsSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     *
     * @throws Exception
     */
    public function run()
    {
        DB::beginTransaction();
        try {
            $menu_items = [
                0 => [
                    'menu_id' => '2',
                    'label' => 'Permission Management',
                    'url' => '/permission',
                    'target' => '_self',
                    'icon' => 'lock',
                    'color' => '',
                    'parent_id' => null,
                    'order' => 1,
                ],
                1 => [
                    'menu_id' => '2',
                    'label' => 'Role Management',
                    'url' => '/role',
                    'target' => '_self',
                    'icon' => 'accessibility',
                    'color' => '',
                    'parent_id' => null,
                    'order' => 2,
                ],
                2 => [
                    'menu_id' => '2',
                    'label' => 'User Management',
                    'url' => '/user',
                    'target' => '_self',
                    'icon' => 'person',
                    'color' => '',
                    'parent_id' => null,
                    'order' => 3,
                ],
                3 => [
                    'menu_id' => '2',
                    'label' => 'Menu Management',
                    'url' => '/menu',
                    'target' => '_self',
                    'icon' => 'menu',
                    'color' => '',
                    'parent_id' => null,
                    'order' => 4,
                ],
            ];

            $new_menu_items = [];
            foreach ($menu_items as $key => $value) {
                $menu_item = DB::table('menu_items')
                        ->where('menu_id', $value['menu_id'])
                        ->where('url', $value['url'])
                        ->first();

                if (isset($menu_item)) {
                    continue;
                }
                $value['id'] = $key + 1;
                $value['created_at'] = '2021-01-01 15:26:06';
                $value['updated_at'] = '2021-01-01 15:26:06';
                $new_menu_items[] = $value;
            }

            DB::table('menu_items')->insert($new_menu_items);
            DB::commit();
        } catch (Exception $e) {
            throw new Exception('Exception occur '.$e);
            DB::rollBack();
        }
    }
}
