<?php

namespace App\Exports\Sheets;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventMasterCategoryCode;
use App\Models\ArcheryEvent;
use App\Models\User;
use App\Models\City;
use App\Models\Provinces;
use App\Models\ArcheryEventIdcardTemplate;
use App\Models\ArcheryEventSerie;
use App\Models\ArcherySeriesCategory;
use Maatwebsite\Excel\Concerns\FromCollection;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithDrawings;
use App\Models\ArcherySeriesUserPoint;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryUserAthleteCode;
use Maatwebsite\Excel\Events\AfterSheet;
use DateTime;

class ArcheryEventSerieUserPointSheet implements FromView, WithColumnWidths, WithHeadings
{
    protected $serie_category_id, $serie_category_label;

    function __construct($serie_category_id, $serie_category_label)
    {
        $this->serie_category_id = $serie_category_id;
        $this->serie_category_label = $serie_category_label;
    }

    public function view(): View
    {
        $serie_category_id = $this->serie_category_id;
        $serie_category_label = $this->serie_category_label;

        $participant_ranked = ArcherySeriesUserPoint::getUserSeriePointByCategory($serie_category_id);
        $datas = [];
        foreach ($participant_ranked as $key => $value) {
            $datas[] = [
                "pos" => $key + 1,
                "name" => $value["user"]["name"],
                "email" => $value["user"]["email"],
                "city" => $value["user"]["city"],
                "date_of_birth" => $value["user"]["date_of_birth"],
                "point_qualification" => isset($value["point_details"]["qualification"]) ? $value["point_details"]["qualification"] : 0,
                "point_elimination" => isset($value["point_details"]["elimination"]) ? $value["point_details"]["elimination"] : 0,
                "total_point" => $value["total_point"],
                "total_score_qualification" => $value["total_score_qualification"],
                "x_y_qualification" => $value["x_y_qualification"],
                "total_per_series" => $value["total_per_series"]
            ];
        }
        return view('reports.serie_user_points', [
            'datas' => $datas,
            'category' => $serie_category_label
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
            'B' => 30,
            'C' => 20,
            'D' => 30,
            'E' => 30,
            'F' => 20,
            'G' => 30,
            'H' => 30,
            'I' => 25,
            'J' => 20,
            'K' => 30,
            'L' => 30,
            'M' => 25,
            'N' => 30,
            'O' => 30,
            'P' => 20,
            'Q' => 30,
        ];
    }
}
