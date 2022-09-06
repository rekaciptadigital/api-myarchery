<?php

namespace App\Exports\Sheets;

use App\Models\ArcherySeriesCategory;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths; 
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class MemberSeriesSheet implements FromView, WithColumnWidths, WithHeadings, WithTitle
{
    protected  $data;

    public function __construct($data) {
        $this->data = $data;
        // dd($data);
    }

    public function view(): View
    {
        $data = $this->data;

        return view('reports.member_series_rank', [
            'data' => $data,
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

    public function title(): string
    {
        $label = "";
        $category_series = ArcherySeriesCategory::join('archery_master_age_categories', 'archery_master_age_categories.id', '=', 'archery_serie_categories.age_category_id')
        ->join('archery_master_competition_categories', 'archery_master_competition_categories.id', '=', 'archery_serie_categories.competition_category_id')
        ->join('archery_master_distances', 'archery_master_distances.id', '=', 'archery_serie_categories.distance_id')
        ->join('archery_master_team_categories', 'archery_master_team_categories.id', '=', 'archery_serie_categories.team_category_id')
        ->select(
            "archery_master_age_categories.label as label_age_categories",
            "archery_master_competition_categories.label as label_competition_categories",
            "archery_master_distances.label as label_distance",
            "archery_master_team_categories.label as label_team"
        )
        ->where('archery_serie_categories.id', $this->data["category_series_id"])
        ->first();
        if ($category_series) {
            $label = $category_series->label_age_categories . " - " . $category_series->label_competition_categories . " - " . $category_series->label_distance . " - " . $category_series->label_team;
        }
        return $label;
    }
    
}


