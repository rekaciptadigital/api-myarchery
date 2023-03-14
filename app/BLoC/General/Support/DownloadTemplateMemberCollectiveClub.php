<?php

namespace App\BLoC\General\Support;

use App\Exports\MemberExportClub;
use Maatwebsite\Excel\Facades\Excel;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Storage;

class DownloadTemplateMemberCollectiveClub extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        // tangkap query event id
        $event_id = $parameters->get("event_id");
        $file_name = "member_collective_club_" . $event_id . "_" . time() . "_.xlsx";
        $final_doc = '/member_collective/' . $event_id . '/' . $file_name;
        $excel = new MemberExportClub($event_id);
        Excel::store($excel, $final_doc, 'public');
        $destinationPath = Storage::url($final_doc);
        $file_path = env('STOREG_PUBLIC_DOMAIN') . $destinationPath;
        return $file_path;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|exists:archery_events,id"
        ];
    }
}
