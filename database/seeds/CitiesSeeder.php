<?php

use Illuminate\Database\CsvtoArray;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Seeder;

class CitiesSeeder extends Seeder
{
    public function run()
    {
    	$Csv = new CsvtoArray;
        $file = __DIR__. '/csv/cities.csv';
        $header = array('id', 'province_id', 'name');
        $data = $Csv->csv_to_array($file, $header);
        $collection = collect($data);
        foreach($collection->chunk(50) as $chunk) {
            \DB::table('cities')->insert($chunk->toArray());
        }
    }
}
