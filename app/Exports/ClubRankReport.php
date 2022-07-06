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
         dd($data);
    }

    public function view(): View
    {
        return view('reports.dashboard_dos.qualification.individual', [
            'title_header' => $this->data['title_header']
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
            'B' => 30,            
            'C' => 20,   
            'D' => 15,
            'E' => 25,
            'F' => 15,
            'G' => 10,
            'H' => 20
        ];
    }
}