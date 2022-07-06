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
            'data' => $this->data['title_header']['category']
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