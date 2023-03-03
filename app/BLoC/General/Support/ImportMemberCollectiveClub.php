<?php

namespace App\BLoC\General\Support;

use App\Imports\MemberCollectiveClubImport;
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
        try {
            $import = new MemberCollectiveClubImport();
            Excel::import($import, $file);
        } catch (BLoCException $e) {
            $message = $e->getMessage();
            $error = $e->errors();
            throw new BLoCException($message, $error);
        }
    }

    protected function validation($parameters)
    {
        return [
            "file" => "required"
        ];
    }
}
