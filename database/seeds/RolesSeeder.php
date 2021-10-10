<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
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
            $roles = [
                0 => [
                    'name' => 'superadmin',
                    'display_name' => 'Super Administrator',
                    'description' => 'Developer Only',
                ],
                1 => [
                    'name' => 'administrator',
                    'display_name' => 'Administrator',
                    'description' => 'Web Administrator',
                ],
                2 => [
                    'name' => 'archery_athlete',
                    'display_name' => 'Atlit Panahan',
                    'description' => 'Pengguna Aplikasi',
                ],
                3 => [
                    'name' => 'event_organizer',
                    'display_name' => 'Event Organizer',
                    'description' => 'Event Organizer',
                ],
                4 => [
                    'name' => 'scorer',
                    'display_name' => 'Scorer',
                    'description' => 'scorer',
                ],
            ];

            $new_roles = [];
            foreach ($roles as $key => $value) {
                $value['id'] = $key + 1;
                $role = DB::table('roles')
                        ->where('id', $value['id'])
                        ->first();

                if (isset($role)) {
                    continue;
                }
                $value['created_at'] = '2021-01-01 15:26:06';
                $value['updated_at'] = '2021-01-01 15:26:06';
                $new_roles[] = $value;
            }

            DB::table('roles')->insert($new_roles);

            DB::commit();
        } catch (Exception $e) {
            throw new Exception('Exception occur '.$e);
            DB::rollBack();
        }
    }
}
