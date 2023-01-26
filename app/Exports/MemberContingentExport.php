<?php

namespace App\Exports;

use DateTime;
use DateTimeZone;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class MemberContingentExport implements FromView, WithColumnWidths, WithColumnFormatting
{
    use Exportable;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        $new_user = [];
        foreach ($this->data as $key => $d) {
            $datetime_zone = new DateTimeZone("Asia/Jakarta");
            $datetime = new DateTime($d["date_of_birth"], $datetime_zone);
            $d["date_of_birth"] = Date::dateTimeToExcel($datetime);
            $new_user[] = $d;
        }

        return view('member_contingent_export', [
            'data' => $new_user
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 40,
            'B' => 25,
            'C' => 40,
            'D' => 25,
            'E' => 25,
            'F' => 25,
            'G' => 25,
            'H' => 40,
            'I' => 40,
            'J' => 40,
            'K' => 40,
            'L' => 25,
            'M' => 40
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_DATE_YYYYMMDD2,
            'E' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
            'L' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
