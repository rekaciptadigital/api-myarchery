<?php

use App\Models\Admin;
use App\Models\AdminRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuperadminSeeder extends Seeder
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
            $admin = new Admin();
            $admin->name = 'Developer Superadmin';
            $admin->email = 'developer-superadmin@archery.com';
            $admin->password = '$2y$10$droXr42bDgp8DQ2yH7kRPOCziZKFFkEIROgWL1mFGwPthauMkbnai';
            $admin->save();

            $admin_role = new AdminRole();
            $admin_role->admin_id = $admin->id;
            $admin_role->role_id = 1;
            $admin_role->save();
            DB::commit();
        } catch (Exception $e) {
            throw new Exception('Exception occur '.$e);
            DB::rollBack();
        }
    }
}
