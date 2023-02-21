<?php

namespace App\Exports\Sheets;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BudrestMemberSheet implements FromView, WithColumnWidths, WithHeadings
{
    private $data;

    function __construct($value)
    {
        $this->data = $value;
    }

    public function view(): View
    {
        $data = $this->data;

        return view('reports.budrest_member', [
            'datas' => $data,
        ]);
    }

    public function headings(): array
    {
        return [
            'A' =>200,
            'B' => 200, 
            'C' => 200          
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 35,            
            'C' => 50
        ];
    }
    
}


