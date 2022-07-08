<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Storage;

class ClubRankReport implements FromView, WithColumnWidths, WithHeadings
{
    use Exportable;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
        //  dd($data);
    }

    public function view(): View
    {
        return view('reports.club_rank', [
            'headers' => $this->data['title_header']['category'],
            'datatables' => $this->data['datatable'],
            'array_of_total_medal_by_category' => $this->data['array_of_total_medal_by_category'],
            'array_of_total_medal_by_category_all_club' => $this->data['array_of_total_medal_by_category_all_club']
        ]);
    }

    public function headings(): array
    {
        return [
            'A' => 10,
            'B' => 10,
            'C' => 85
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 40,
        ];
    }
}
