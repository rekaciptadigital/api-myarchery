<?php

namespace App\Exports;

use App\Exports\Sheets\MemberSeriesSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MemberSeriesExport implements WithMultipleSheets
{
    use Exportable;
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->data as $value) {
            $sheets[] = new MemberSeriesSheet($value);
        }

        return $sheets;
    }
}
