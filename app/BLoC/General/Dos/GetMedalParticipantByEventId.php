<?php

namespace App\BLoC\General\Dos;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventQualificationTime;
use DAI\Utils\Abstracts\Retrieval;


class GetMedalParticipantByEventId extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get("event_id");
        $list_category_with_day = ArcheryEventQualificationTime::getCategoryByDate($event_id);
        $all_data = [];
        foreach ($list_category_with_day as $key1 => $value1) {
            $data_all_category_in_day = [];
            foreach ($value1["category"] as $key2 => $value2) {
                $category_detail = ArcheryEventCategoryDetail::find($value2->id);
                $category_team_type = $value2->getCategoryType();

                $data_qualification = ArcheryEventParticipant::getQualification($category_detail->id);
                $data_report_qualification_individu = ArcheryEventParticipant::getData($category_detail->id, "qualification", $event_id);
                $data_report_by_team_qualification_individu = [];

                // ====================== qualification ==========================
                if (strtolower($category_team_type) == "individual") {
                    if (!empty($data_report_qualification_individu[0])) {
                        $data_report_by_team_qualification_individu["team"] = "individual";
                        $data_report_by_team_qualification_individu["data"] = $data_report_qualification_individu;
                        $data_report_by_team_qualification_individu["type"] = "qualification";
                        $data_all_category_in_day[] = $data_report_by_team_qualification_individu;
                    }
                }

                $data_elimination_team = ArcheryEventParticipant::getDataEliminationTeam($category_detail->id);
                $data_report_by_team_qualification_team = [];
                if (strtolower($category_team_type) == "team") {
                    if ($data_elimination_team == []) {
                        $new_data_qualification_best_of_three = [];
                        foreach ($data_qualification as $dq) {
                            $new_data_qualification_best_of_three[] = $dq;
                            if (count($new_data_qualification_best_of_three) == 3) {
                                break;
                            }
                        }
                        $data_report_by_team_qualification_team["team"] = "team";
                        $data_report_by_team_qualification_team["data"] = $new_data_qualification_best_of_three;
                        $data_report_by_team_qualification_team["category_label"] = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id);
                        $data_report_by_team_qualification_team["type"] = "qualification";
                        $data_all_category_in_day[] = $data_report_by_team_qualification_team;
                    }
                }
                // ================================ end qualification ==========================

                // ================================ elimination ==================================
                $data_report_by_team_elimination_individu = [];
                $data_report_elimination_individu = ArcheryEventParticipant::getData($category_detail->id, "elimination", $event_id);
                if (strtolower($category_team_type) == "individual") {
                    if (!empty($data_report_elimination_individu[0])) {
                        $data_report_by_team_elimination_individu["team"] = "individual";
                        $data_report_by_team_elimination_individu["data"] = $data_report_elimination_individu;
                        $data_report_by_team_elimination_individu["type"] = "elimination";
                        $data_report_by_team_elimination_individu["category_label"] = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id);
                        $data_all_category_in_day[] = $data_report_by_team_elimination_individu;
                    }
                }

                $data_report_by_team_elimination_team = [];
                if (strtolower($category_team_type) == "team") {
                    $data_elimination_team = ArcheryEventParticipant::getDataEliminationTeam($category_detail->id);
                    if (!empty($data_elimination_team)) {
                        $data_report_by_team_elimination_team["team"] = "team";
                        $data_report_by_team_elimination_team["data"] = $data_elimination_team;
                        $data_report_by_team_elimination_team["type"] = "elimination";
                        $data_report_by_team_elimination_team["category_label"] = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id);
                        $data_all_category_in_day[] = $data_report_by_team_elimination_team;
                    }
                }

                // ================================end elimination ===============================


            }
            $all_data[] = [
                'data_report' => $data_all_category_in_day,
                'day' => $value1["day"]
            ];
        }

        return $all_data;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|exists:archery_events,id"
        ];
    }
}
