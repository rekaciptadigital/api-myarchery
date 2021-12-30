<?php

use Illuminate\Database\CsvtoArray;
use Illuminate\Database\Seeder;

class ProvincesSeeder extends Seeder
{
    public function run()
    {
		$Csv = new CsvtoArray;
        $file = __DIR__. '/csv/provinces.csv';
        error_log($file);
        $header = array('id', 'name');
        $data = $Csv->csv_to_array($file, $header);
        \DB::table('provinces')->insert($data);
    }
}
