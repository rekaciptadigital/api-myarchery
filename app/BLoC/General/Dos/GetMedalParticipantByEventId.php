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
        $dates = [];
        foreach ($list_category_with_day as $key1 => $value1) {

            $data_qualification_all = [];
            $data_elimination_all = [];
            foreach ($value1["category"] as $key2 => $value2) {
                $category_detail = ArcheryEventCategoryDetail::find($value2->id);
                $category_team_type = $value2->getCategoryType();

                $data_report_qualification_individu = ArcheryEventParticipant::getData($category_detail->id, "qualification", $event_id);
                $data_report_by_team_qualification_individu = [];

                // ====================== qualification ==========================
                if (strtolower($category_team_type) == "individual") {
                    if (!empty($data_report_qualification_individu[0])) {
                        $array_member = [];
                        foreach ($data_report_qualification_individu[0] as $key => $athlete) {
                            $member = [
                                "name" => $athlete["athlete"],
                                "club" => $athlete["club"],
                                "medal" => $athlete["medal"],
                                "rank" => $key + 1,
                            ];
                            array_push($array_member, $member);
                        }

                        $data_report_by_team_qualification_individu["members"] = $array_member;
                        $data_report_by_team_qualification_individu["category_label"] = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id);
                        $data_qualification_all[] = $data_report_by_team_qualification_individu;
                    }
                }

                $data_elimination_team = ArcheryEventParticipant::getDataEliminationTeam($category_detail->id);
                $data_report_by_team_qualification_team = [];
                if (strtolower($category_team_type) == "team") {
                    $data_qualification = ArcheryEventParticipant::getQualification($category_detail->id);
                    if ($data_elimination_team == null && $data_qualification != []) {
                        $new_data_qualification_best_of_three = [];

                        foreach ($data_qualification as $k => $dq) {
                            $athlete = [];
                            foreach ($dq["teams"] as $value) {
                                array_push($athlete, $value["name"]);
                            }
                            $res = [
                                "athlete" => $athlete,
                                "club_name" => $dq["team"],
                                "rank" => $k + 1
                            ];
                            $new_data_qualification_best_of_three[] = $res;
                            if (count($new_data_qualification_best_of_three) == 3) {
                                break;
                            }
                        }
                        $data_report_by_team_qualification_team["list_team"] = $new_data_qualification_best_of_three;
                        $data_report_by_team_qualification_team["category_label"] = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id);
                        $data_qualification_all[] = $data_report_by_team_qualification_team;
                    }
                }
                // ================================ end qualification ==========================

                // ================================ elimination ==================================
                $data_report_by_team_elimination_individu = [];
                $data_report_elimination_individu = ArcheryEventParticipant::getData($category_detail->id, "elimination", $event_id);
                if (strtolower($category_team_type) == "individual") {
                    if (!empty($data_report_elimination_individu[0])) {
                        $athlete = [];
                        $response_athlete = [];
                        foreach ($data_report_elimination_individu[0] as $key => $value) {
                            $response_athlete = [
                                "name" => $value["athlete"],
                                "club" => $value["club"],
                                "medal" => $value["medal"],
                                "rank" => $key + 1
                            ];
                            array_push($athlete, $response_athlete);
                        }
                        $data_report_by_team_elimination_individu["athlete"] = $athlete;
                        $data_report_by_team_elimination_individu["category_label"] = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id);
                        $data_elimination_all[] = $data_report_by_team_elimination_individu;
                    }
                }

                $data_report_by_team_elimination_team = [];
                if (strtolower($category_team_type) == "team") {
                    $data_elimination_team = ArcheryEventParticipant::getDataEliminationTeam($category_detail->id);
                    if (!empty($data_elimination_team)) {
                        $response_tim = [];
                        $array_tim = [];
                        $array_member = [];
                        foreach ($data_elimination_team as $key => $value) {
                            $response_tim["team_name"] = $value["team_name"];
                            foreach ($value["member_team"] as $key => $value) {
                                $array_member[] = $value["name"];
                            }
                            $response_tim["member_team"] = $array_member;
                            $response_tim["rank"] = $key + 1;
                            $array_tim[] = $response_tim;
                        }
                        $data_report_by_team_elimination_team["list_team"] = $array_tim;
                        $data_report_by_team_elimination_team["category_label"] = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id);
                        $data_elimination_all[] = $data_report_by_team_elimination_team;
                    }
                }

                // ================================end elimination ===============================


            }
            $dates[$value1["date_format"]] = [
                "qualification" => $data_qualification_all,
                "elimination" => $data_elimination_all
            ];
        }
        return $dates;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|exists:archery_events,id"
        ];
    }
}
