<?php

namespace App\Exports;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEvent;
use App\Models\User;
use App\Models\ArcheryEventIdcardTemplate;
use Maatwebsite\Excel\Concerns\FromCollection;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths; 
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryUserAthleteCode;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Exports\Sheets\SummaryParticipantSheet;
use App\Exports\Sheets\ArcheryEventParticipantSheet;
use \Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Concerns\WithEvents;

class ArcheryEventParticipantExport implements WithMultipleSheets
{
    protected $event_id,$status_id;

    function __construct($event_id) {
            $this->event_id = $event_id;
    }

    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new SummaryParticipantSheet($this->event_id);
        $sheets[] = new ArcheryEventParticipantSheet($this->event_id);
        return $sheets;
    }
}


