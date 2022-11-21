<?php

namespace App\BLoC\Web\EndpointSupport;

use App\Exports\UserBudrestExport;
use App\Imports\UserBudrestImport;
use DAI\Utils\Abstracts\Retrieval;
use Maatwebsite\Excel\Facades\Excel;

class InsertMemberBudrestByCsv extends Retrieval
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

        $file_name = "import_user_budrest/" . time() . ".csv";
        Excel::store(new UserBudrestExport($data), $file_name);
        Excel::import(new UserBudrestImport($event_id), $file_name);
        return "success";
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => "required|exists:archery_events,id",
            'base_64' => 'required',
        ];
    }
}
