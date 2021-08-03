<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserExampleSeeder extends Seeder
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
            $user->name = 'Developer user';
            $user->email = 'developer-user@archery.com';
            $user->password = '$2y$10$droXr42bDgp8DQ2yH7kRPOCziZKFFkEIROgWL1mFGwPthauMkbnai';
            $user->save();

            DB::commit();
        } catch (Exception $e) {
            throw new Exception('Exception occur '.$e);
            DB::rollBack();
        }
    }
}
