<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VenueMasterPlaceFacilitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        try {
            $venue_master_place_facilities = [
                [
                  'name' => "Mushola",
                ],
                [
                  'name' => "Toilet",
                ],
                [
                  'name' => "Restoran",
                ],
                [
                  'name' => "Cafe",
                ],
                [
                  'name' => "Parkir",
                ],
                [
                  'name' => "Wi-Fi",
                ],
                [
                  'name' => "Ruang Ganti",
                ],
                [
                  'name' => "Tribun",
                ],
            ];
    
            DB::table('venue_master_place_facilities')->insert($venue_master_place_facilities);
            DB::commit();
        } catch (Exception $e) {
            throw new Exception('Exception occur ' . $e);
            DB::rollBack();
        }
        
    }
}
