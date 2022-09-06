<?php

namespace App\BloC\General\Series;

use App\Exports\MemberSeriesExport;
use App\Models\ArcherySeriesCategory;
use App\Models\ArcherySeriesUserPoint;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExportMemberSeriesRank extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $series_id = $parameters->get("series_id");
        $category_series = ArcherySeriesCategory::where("serie_id", $series_id)->get();
        $data = [];
        foreach ($category_series as $cat_series) {
            $response = [];
            $response["category_series_id"] = $cat_series->id;
            $response["list_member_point"] = ArcherySeriesUserPoint::getUserSeriePointByCategory($cat_series->id);
            $data[] = $response;
        }

        $file_name = "series_rank_".$series_id;
        $final_doc = '/Series/' . $series_id . '/' . $file_name . '.xlsx';
        $excel = new MemberSeriesExport($data);
        $download = Excel::store($excel, $final_doc, 'public');
        $destinationPath = Storage::url($final_doc);
        $file_path = env('STOREG_PUBLIC_DOMAIN') . $destinationPath;
        return $file_path;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
