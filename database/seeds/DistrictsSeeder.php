<?php

use Illuminate\Database\CsvtoArray;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictsSeeder extends Seeder
{
    public function run()
    {
    	$Csv = new CsvtoArray;
        $file = __DIR__. '/csv/districts.csv';
        $header = array('id', 'city_id', 'name');
        $data = $Csv->csv_to_array($file, $header);
        $collection = collect($data);
        foreach($collection->chunk(50) as $chunk) {
            DB::table('districts')->insert($chunk->toArray());
        }
    }
}
