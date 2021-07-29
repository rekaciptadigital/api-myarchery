<?php

use App\Models\User;
use App\Models\UserRole;
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
            $user = new User();
            $user->name = 'Developer Superadmin';
            $user->email = 'developer-superadmin@archery.com';
            $user->password = '$2y$10$droXr42bDgp8DQ2yH7kRPOCziZKFFkEIROgWL1mFGwPthauMkbnai';
            $user->save();

            $user_role = new UserRole();
            $user_role->user_id = $user->id;
            $user_role->role_id = 1;
            $user_role->save();
            DB::commit();
        } catch (Exception $e) {
            throw new Exception('Exception occur '.$e);
            DB::rollBack();
        }
    }
}
