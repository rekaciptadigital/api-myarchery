<?php

namespace App\Exports\Sheets;

use App\Models\ArcheryEventCategoryDetail;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ListCategoryIdSheet implements FromView, WithColumnWidths, WithHeadings, WithTitle
{

    protected $event_id;

    public function __construct(int $event_id)
    {
        $this->event_id = $event_id;
    }

    public function view(): View
    {
        $data = ArcheryEventCategoryDetail::where("event_id", $this->event_id)->get();
        return view('sheets.list_category_id', [
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
        return 'Category ID';
    }
}
