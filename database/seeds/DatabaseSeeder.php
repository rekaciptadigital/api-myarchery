<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RolesSeeder::class);
        $this->call(PermissionsSeeder::class);
        $this->call(RolePermissionsSeeder::class);
        $this->call(SuperadminSeeder::class);
        $this->call(UserExampleSeeder::class);
        $this->call(MenusSeeder::class);
        $this->call(MenuItemsSeeder::class);
        $this->call(MenuItemPermissionsSeeder::class);
        $this->call(ArcheryMasterDataSeeder::class);
        $this->call(ProvincesSeeder::class);
        $this->call(CitiesSeeder::class);
        $this->call(DistrictsSeeder::class);
        $this->call(VillagesSeeder::class);
        $this->call(VenueMasterPlaceFacilitiesSeeder::class);
        $this->call(VenueMasterPlaceCapacityAreaSeeder::class);
    }
}
