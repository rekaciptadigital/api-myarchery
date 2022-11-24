<?php

namespace App\BLoC\Web\EndpointSupport;

use App\Exports\MemberScoringExport;
use App\Exports\UserBudrestExport;
use App\Imports\MemberScoringImport;
use App\Imports\UserBudrestImport;
use DAI\Utils\Abstracts\Retrieval;
use Maatwebsite\Excel\Facades\Excel;

class InserSkoringByExcell extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get("event_id");
        $base_64_decode = base64_decode($parameters->get("base_64"));

        $rows = explode("\n", $base_64_decode);

        $data = [];
        foreach ($rows as $key => $row) {
            $array_string = explode(",", $row);
            $collection = [];
            foreach ($array_string as $key => $value) {
                $collection[] = $value;
            }
            $data[] = $collection;
        }

        $file_name = "import_user_scoring/" . time() . ".csv";
        Excel::store(new MemberScoringExport($data), $file_name);
        $import = new MemberScoringImport($event_id);
        Excel::import($import, $file_name);
        return $import->getFailImport();
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => "required|exists:archery_events,id",
            'base_64' => 'required',
        ];
    }
}
