<?php

namespace App\BLoC\Web\ArcheryEventOfficial;

use App\Models\User;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventOfficialDetail;
use App\Libraries\PdfLibrary;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Auth;
use Mpdf\Output\Destination;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryEventOfficial;

class GetArcheryEventOfficialDetail extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();

        $official = ArcheryEventOfficialDetail::find($parameters->get('id'));

        if (!$official) {
            throw new BLoCException("data not found");
        }

        return $official;
    }

    protected function validation($parameters)
    {
        return [
            'id' => [
                'required'
            ],

        ];
    }
}
