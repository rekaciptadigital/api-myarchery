<?php

namespace App\BLoC\Web\UpdateParticipantByAdmin;

use App\Exports\ParticipantExport;
use App\Imports\ParticipantImport;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Maatwebsite\Excel\Facades\Excel;

class ImportParticipantExcell extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $file = $parameters->get("provinces");
        $base_64 = base64_decode($parameters->get("csv_file"), true);
        if ($base_64 === false) {
            throw new BLoCException("is not base64");
        }

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

        // membuat nama file unik
        $nama_file = rand() . $file->getClientOriginalName();

        $file->move('file_siswa', $nama_file);
        // Excel::store(new ParticipantExport($data), 'users.csv',);
        Excel::import(new ParticipantImport, public_path('/file_siswa/' . $nama_file));
    }

    protected function validation($parameters)
    {
        return [];
    }
}
