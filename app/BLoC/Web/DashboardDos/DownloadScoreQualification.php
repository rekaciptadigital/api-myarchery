<?php

namespace App\BLoC\Web\DashboardDos;

use App\Models\ArcheryEvent;
use App\Models\ArcheryScoring;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryMasterTeamCategory;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\DB;
use App\Exports\ParticipantScoreQualification;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class DownloadScoreQualification extends Retrieval
{
    var $total_per_points = [
        "" => 0,
        "1" => 0,
        "2" => 0,
        "3" => 0,
        "4" => 0,
        "5" => 0,
        "6" => 0,
        "7" => 0,
        "8" => 0,
        "9" => 0,
        "10" => 0,
        "x" => 0,
        "m" => 0,
    ];

    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $score_type = 1;
        $name = $parameters->get("name");
        $event_category_id = $parameters->get('event_category_id');
        $filter_session = $parameters->get('session');

        $category_detail = ArcheryEventCategoryDetail::find($event_category_id);
        if (!$category_detail) {
            throw new BLoCException("category tidak ditemukan");
        }

        $team_category = ArcheryMasterTeamCategory::find($category_detail->team_category_id);
        if (!$team_category) {
            throw new BLoCException("team category not found");
        }

        $event = ArcheryEvent::find($category_detail->event_id);
        if (!$event) {
            throw new BLoCException("CATEGORY INVALID");
        }

        if ($filter_session > $category_detail->session_in_qualification) {
            throw new BLoCException("Sesi ini tidak tersedia");
        }

        $session = [];
        for ($i=0; $i < $category_detail->session_in_qualification; $i++) { 
            $session[] = $i+1;
        }
        
        if ($category_detail->category_team == "Individual") {
            $data = $this->getListMemberScoringIndividual($event_category_id, $score_type, $session, $name, $event->id);
            return $this->download($data,$category_detail->event_name, $filter_session, $category_detail->session_in_qualification, $category_detail->label_category, 'individual');
        }

        if (strtolower($team_category->type) == "team") {
            if ($team_category->id == "mix_team") {
                $data = ArcheryEventParticipant::mixTeamBestOfThree($category_detail);
            } else {
                $data = ArcheryEventParticipant::teamBestOfThree($category_detail);
            }
            return $this->download($data, $category_detail->event_name, $filter_session, $category_detail->session_in_qualification, $category_detail->label_category, 'team');
        }
    }


    protected function validation($parameters)
    {
        return [
            "event_category_id" => "required"
        ];
    }

    private function getListMemberScoringIndividual($category_id, $score_type, $session, $name, $event_id)
    {
        $qualification_member = ArcheryScoring::getScoringRankByCategoryId($category_id, $score_type, $session, false, $name, false, 1);
        $category = ArcheryEventCategoryDetail::find($category_id);

        $qualification_rank = ArcheryScoring::getScoringRank($category->distance_id, $category->team_category_id, $category->competition_category_id, $category->age_category_id, $category->gender_category, $score_type, $event_id);

        $response = [];

        foreach ($qualification_member as $key1 => $value1) {
            foreach ($qualification_rank as $key2 => $value2) {
                if ($value1["member"]["id"] === $value2["member"]["id"]) {
                    $value1["rank"] = $key2 + 1;
                    $value1["have_shoot_off"] = $value2["have_shoot_off"];
                    array_push($response, $value1);
                    break;
                }
            }
        }

        return $response;
    }

    private function download($response, $event_name, $filter_session, $session_in_qualification, $category_name, $type)
    {
        $file_name = $event_name . "_" . $category_name . "_" . date("YmdHis");
        $final_doc = '/score-qualification/' . $event_name . '/' . $file_name . '.xlsx';

        $data = [
            "type" => $type,
            "response" => $response,
            "event_name" => $event_name,
            "filter_session" => $filter_session,
            "session_in_qualification" => $session_in_qualification
        ];
    
        $excel = new ParticipantScoreQualification($data);
        $download= Excel::store($excel, $final_doc, 'public');
       
        $destinationPath = Storage::url($final_doc);
        $file_path = env('STOREG_PUBLIC_DOMAIN').$destinationPath;
        return $file_path;
    }
}
