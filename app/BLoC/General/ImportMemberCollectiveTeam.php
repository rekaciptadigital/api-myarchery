<?php

namespace App\BLoC\General;

use App\Imports\MemberCollectiveTeamImport;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Maatwebsite\Excel\Facades\Excel;

class ImportMemberCollectiveTeam extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $file = $parameters->get("file");
        try {
            $import = new MemberCollectiveTeamImport();
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
            "file" => "required|mimes:xlsx"
        ];
    }
}