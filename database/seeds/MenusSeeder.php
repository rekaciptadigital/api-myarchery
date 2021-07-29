<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenusSeeder extends Seeder
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
            $menus = [
                0 => [
                    'key' => 'default_menu',
                    'display_name' => 'Menu',
                    'description' => 'Menu for Web Administrator',
                ],
                1 => [
                    'key' => 'configuration',
                    'display_name' => 'Configuration',
                    'description' => 'Menu for Super Administrator',
                ],
            ];

            $new_menus = [];
            foreach ($menus as $key => $value) {
                $menu = DB::table('menus')
                        ->where('key', $value['key'])
                        ->first();

                if (isset($menu)) {
                    continue;
                }
                $value['id'] = $key + 1;
                $value['created_at'] = '2021-01-01 15:26:06';
                $value['updated_at'] = '2021-01-01 15:26:06';
                $new_menus[] = $value;
            }

            DB::table('menus')->insert($new_menus);
            DB::commit();
        } catch (Exception $e) {
            throw new Exception('Exception occur '.$e);
            DB::rollBack();
        }
    }
}
