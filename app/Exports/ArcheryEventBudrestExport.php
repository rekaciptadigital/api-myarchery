<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\BudrestMemberSheet;

class ArcheryEventBudrestExport implements WithMultipleSheets
{
    use Exportable;

    protected $datas;

    function __construct($data)
    {
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
