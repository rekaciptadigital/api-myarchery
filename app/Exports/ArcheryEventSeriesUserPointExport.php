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
use App\Exports\Sheets\ArcheryEventSerieUserPointSheet;
use App\Exports\Sheets\ArcheryEventSerieUserPoint;
use \Maatwebsite\Excel\Sheet;
use App\Models\ArcherySeriesCategory;
use App\Models\ArcherySeriesUserPoint;
use Maatwebsite\Excel\Concerns\WithEvents;

class ArcheryEventSeriesUserPointExport implements WithMultipleSheets
{
    protected $serie_id;

    function __construct($serie_id) {
            $this->serie_id = $serie_id;
    }

    public function sheets(): array
    {
        $categories = ArcherySeriesCategory::where("serie_id",$this->serie_id)->get();

        $sheets = [];
        foreach ($categories as $key => $value) {
            $sheets[] = new ArcheryEventSerieUserPointSheet($value->id, $value->getCategoryLabelAttribute());
        }
        return $sheets;
    }
}


