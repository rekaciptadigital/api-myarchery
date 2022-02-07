<?php

namespace App\BLoC\Web\ArcheryEventParticipant;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEvent;
use App\Models\ArcheryQualificationSchedules;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventS;
use DAI\Utils\Abstracts\Retrieval;
use App\Exports\ArcheryEventParticipantStatusLunasExportAll;
use Maatwebsite\Excel\Facades\Excel;


class GetDownloadArcheryEventParticipantLunasAll extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
      $event_id = $parameters->get('event_id');
      $download= Excel::download(new ArcheryEventParticipantStatusLunasExportAll(), 'invoices.xlsx');
      return $download;
    }

   

}


