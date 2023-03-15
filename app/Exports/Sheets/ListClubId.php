<?php

namespace App\Exports\Sheets;

use App\Models\ArcheryClub;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ListClubId implements FromView, WithColumnWidths, WithHeadings, WithTitle
{

    public function view(): View
    {
        $data = ArcheryClub::all();
        return view('sheets.list_club_id', [
            "data" => $data
        ]);
    }

    public function headings(): array
    {
        return [
            'A' => 200,
            'B' => 200,
            'C' => 200
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 35,
        ];
    }

       /**
     * @return string
     */
    public function title(): string
    {
        return 'Club ID';
    }
}
