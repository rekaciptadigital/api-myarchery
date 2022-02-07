<?php

namespace App\BLoC\Web\ArcheryEventParticipant;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEvent;
use App\Models\ArcheryQualificationSchedules;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventS;
use DAI\Utils\Abstracts\Retrieval;
use App\Exports\ArcheryEventParticipantStatusLunasPendingAll;
use Maatwebsite\Excel\Facades\Excel;


class GetDownloadArcheryEventParticipantPendingAll extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
      $download= Excel::download(new ArcheryEventParticipantStatusLunasPendingAll(), 'invoices.xlsx');
      return $download;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required',
        ];
    }

}


