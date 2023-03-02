<?php

use App\Models\ParentClassificationMembers;
use Illuminate\Database\Seeder;

class ParentClassification extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dummy_data = [
            [
                'title' => 'Klub',
            ],
            [
                'title' => 'Negara',
            ],
            [
                'title' => 'Wilayah Provinsi',
            ],
            [
                'title' => 'Wilayah Kota',
            ],
            [
                'title' => 'Dari Peserta',
            ],
        ];

        ParentClassificationMembers::insert($dummy_data);
    }
}
