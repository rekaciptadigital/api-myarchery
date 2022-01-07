<?php

use Illuminate\Database\CsvtoArray;
use Illuminate\Database\Seeder;

class VillagesSeeder extends Seeder
{
    public function run()
    {
    	$Csv = new CsvtoArray;
        $file = __DIR__. '/csv/villages.csv';
        $header = array('id', 'district_id', 'name');
        $data = $Csv->csv_to_array($file, $header);
        $collection = collect($data);
        foreach($collection->chunk(50) as $chunk) {
            \DB::table('villages')->insert($chunk->toArray());
        }
    }

}
