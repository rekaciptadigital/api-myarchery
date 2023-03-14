<?php

namespace App\BLoC\General\Support;

use App\Imports\MemberCollectiveClubImport;
use App\Imports\MemberCollectiveWithClubImport;
use Maatwebsite\Excel\Facades\Excel;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;

class ImportMemberCollectiveClub extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $file = $parameters->get("file");
        $import = new MemberCollectiveWithClubImport();
        Excel::import($import, $file);
    }

    protected function validation($parameters)
    {
        return [
            "file" => "required"
        ];
    }
}
