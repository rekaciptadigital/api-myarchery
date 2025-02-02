<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\Exportable;

class MemberContingentTeamExport implements FromView, WithColumnWidths
{
    use Exportable;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('member_contingent_team_export', [
            'data' => $this->data
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 40,
            'B' => 20,
            'C' => 40,
            'D' => 20,
            'E' => 40,
            'F' => 40,
            'G' => 20
        ];
    }
}
