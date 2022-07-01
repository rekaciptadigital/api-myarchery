<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths; 
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings; 
use Illuminate\Support\Facades\Storage;

class ParticipantScoreQualification implements FromView, WithColumnWidths, WithHeadings
{
    use Exportable;
    
    private $data;

    public function __construct($data)
    {
         $this->data = $data;
    }

    public function view(): View
    {
        if ($this->data['type'] == 'individual') {
            return view('reports.dashboard_dos.qualification.individual', [
                'datas' => $this->data['response'],
                'event_name' => $this->data['event_name'],
                'filter_session' => $this->data['filter_session'],
                'session_in_qualification' => $this->data['session_in_qualification']
            ]);
        } else {
            return view('reports.dashboard_dos.qualification.team', [
                'datas' => $this->data['response'],
                'event_name' => $this->data['event_name'],
                'filter_session' => $this->data['filter_session'],
                'session_in_qualification' => $this->data['session_in_qualification']
            ]);
        }
        
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