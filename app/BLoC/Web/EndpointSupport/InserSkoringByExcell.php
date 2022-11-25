<?php

namespace App\BLoC\Web\EndpointSupport;

use App\Exports\MemberScoringExport;
use App\Imports\MemberScoringImport;
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
        $category_id = $parameters->get("category_id");
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
        $import = new MemberScoringImport($category_id);
        Excel::import($import, $file_name);
        return $import->getFailImport();
    }

    protected function validation($parameters)
    {
        return [
            'category_id' => "required|exists:archery_event_category_details,id",
            'base_64' => 'required',
        ];
    }
}
