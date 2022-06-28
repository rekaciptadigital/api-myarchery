<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths; 
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings; 
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Exports\Sheets\BudrestMemberSheet;
use \Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Concerns\WithEvents;

class ArcheryEventBudrestExport implements WithMultipleSheets
{
    use Exportable;

    protected $data;

    function __construct($data) {
        $this->datas = $data;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->datas['category_budrest'] as $key => $value) {
            $sheets[] = new BudrestMemberSheet($value);
        }

        return $sheets;
    }
}


