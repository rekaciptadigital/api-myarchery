<?php

namespace App\BLoC\Web\UpdateParticipantByAdmin;

use App\Exports\ParticipantExport;
use App\Imports\ParticipantImport;
use DAI\Utils\Abstracts\Transactional;
use Maatwebsite\Excel\Facades\Excel;

class ImportParticipantExcell extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        // $file = $parameters->get("provinces");
        $base_64 = base64_decode($parameters->get("csv_file"), true);

        $rows = explode("\n", $base_64);

        $data = [];
        foreach ($rows as $key => $row) {
            $array_string = explode(",", $row);
            $collection = [];
            foreach ($array_string as $key => $value) {
                $collection[] = $value;
            }
            $data[] = $collection;
        }

        $file_name = "import_participant_excell/users_" . time() . ".csv";
        Excel::store(new ParticipantExport($data), $file_name);
        Excel::import(new ParticipantImport, $file_name);
        return "success";
    }

    protected function validation($parameters)
    {
        return [
            "csv_file" => "required|string"
        ];
    }
}
