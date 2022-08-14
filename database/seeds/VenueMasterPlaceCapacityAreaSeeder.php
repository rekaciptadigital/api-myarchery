<?php

use Illuminate\Database\Seeder;

class VenueMasterPlaceCapacityAreaSeeder extends Seeder
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
            $venue_master_place_capacity_area = [
                [
                  'distance' => "10",
                ],
                [
                  'distance' => "20",
                ],
                [
                  'distance' => "30",
                ],
                [
                  'distance' => "40",
                ],
                [
                  'distance' => "50",
                ],
                [
                  'distance' => "60",
                ],
                [
                  'distance' => "70",
                ]
            ];
    
            DB::table('venue_master_place_capacity_area')->insert($venue_master_place_capacity_area);
            DB::commit();
        } catch (Exception $e) {
            throw new Exception('Exception occur ' . $e);
            DB::rollBack();
        }
        
    }
}
