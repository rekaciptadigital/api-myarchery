<?php

namespace App\Exports\Sheets;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventIdcardTemplate;
use App\Models\User;
use App\Models\ArcheryMasterTeamCategory;

use Maatwebsite\Excel\Concerns\FromCollection;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths; 
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings; 
use Maatwebsite\Excel\Concerns\WithDrawings;
use Illuminate\Support\Facades\DB;

class BudrestMemberSheet implements FromView, WithColumnWidths, WithHeadings
{
    private $value;

    function __construct($value) {
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


