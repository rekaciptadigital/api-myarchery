<?php

namespace App\BLoC\General;

use App\Exports\ClubRankExportByTeamCategory;
use App\Libraries\ClubRanked;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ExportClubRankedGroupByTeamCategory extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        try {
            $event_id = $parameters->get("event_id");
            $event_id = $parameters->get("event_id");
            $rules_rating_club = $parameters->get("rules_rating_club") == null ? 1 : $parameters->get("rules_rating_club");
            $group_category_id = $parameters->get("group_category_id") == null ? 0 : $parameters->get("group_category_id");
            $age_category_id = $parameters->get("age_category_id");
            $competition_category_id = $parameters->get("competition_category_id");
            $distance_id = $parameters->get("distance_id");

            $datatables = ClubRanked::getEventRanked($event_id, $rules_rating_club, $group_category_id, $age_category_id, $competition_category_id, $distance_id);
            $data = ["datatables" => $datatables];

            $file_name = "CLUB_RANK_by_team_cat" . $event_id . '_' . date("YmdHis");
            $final_doc = '/club_rank/' . $event_id . '/' . $file_name . '.xlsx';

            $excel = new ClubRankExportByTeamCategory($data);
            $download = Excel::store($excel, $final_doc, 'public');

            $destinationPath = Storage::url($final_doc);
            $file_path = env('STOREG_PUBLIC_DOMAIN') . $destinationPath;
            return $file_path;
        } catch (Throwable $th) {
            throw new BLoCException($th->getMessage());
        }
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|exists:archery_events,id"
        ];
    }
}
