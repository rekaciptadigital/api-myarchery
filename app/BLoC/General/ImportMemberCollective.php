<?php

namespace App\BLoC\General;

use App\Exports\MemberContingentExport;
use App\Imports\MemberCollectiveImport;
use App\Libraries\Upload;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\City;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportMemberCollective extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $file = $parameters->get("file");
        try {
            $import = new MemberCollectiveImport();
            Excel::import($import, $file);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            return $failures;
        }

    }

    protected function validation($parameters)
    {
        return [
            "file" => "required|mimes:xlsx"
        ];
    }
}