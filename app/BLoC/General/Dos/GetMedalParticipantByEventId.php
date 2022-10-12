<?php

namespace App\BLoC\General\Dos;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventQualificationTime;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

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
                if (!$category_detail) {
                    throw new BLoCException("category not found");
                }
                $category_team_type = $category_detail->getCategoryType();

                $data_report_qualification_individu = ArcheryEventParticipant::getData($category_detail->id, "qualification", $event_id);

                // ====================== qualification ==========================
                if (strtolower($category_team_type) == "individual") {
                    if (!empty($data_report_qualification_individu[0])) {
                        $array_member = [];
                        foreach ($data_report_qualification_individu[0] as $key => $athlete) {
                            $member = [
                                "type" => "individu",
                                "category_id" => $category_detail->id,
                                "winner_name" => $athlete["athlete"],
                                "club_name" => $athlete["club"],
                                "rank" => $key + 1,
                                "participant_id" => $athlete["participant_id"],
                                "category_label" => ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id)
                            ];
                            array_push($data_qualification_all, $member);
                        }
                    }
                }

                $data_elimination_team = ArcheryEventParticipant::getDataEliminationTeam($category_detail->id);
                if (strtolower($category_team_type) == "team") {
                    $data_qualification = ArcheryEventParticipant::getQualification($category_detail->id);
                    if ($data_elimination_team == null && $data_qualification != []) {
                        $new_data_qualification_best_of_three = [];

                        foreach ($data_qualification as $k => $dq) {
                            $athlete = [];
                            foreach ($dq["teams"] as $value) {
                                $res_athlete = [
                                    "name" => $value["name"],
                                    "participant_id" => $value["participant_id"],
                                    "member_id" => $value["id"],
                                    "club_name" => $value["club_name"]
                                ];
                                array_push($athlete, $res_athlete);
                                if (count($athlete) == 3) {
                                    break;
                                }
                            }
                            $res = [
                                "type" => "team",
                                "participant_id" => $dq["participant_id"],
                                "category_id" => $category_detail->id,
                                "list_athlete" => $athlete,
                                "club_name" => $dq["club_name"],
                                "winner_name" => $dq["team"],
                                "rank" => $k + 1,
                                "category_label" => ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id)
                            ];
                            $data_qualification_all[] = $res;
                            $new_data_qualification_best_of_three[] = $res;
                            if (count($new_data_qualification_best_of_three) == 3) {
                                break;
                            }
                        }
                    }
                }
                // ================================ end qualification ==========================

                // ================================ elimination ==================================
                $data_report_elimination_individu = ArcheryEventParticipant::getData($category_detail->id, "elimination", $event_id);
                if (strtolower($category_team_type) == "individual") {
                    if (!empty($data_report_elimination_individu[0])) {
                        $athlete = [];
                        $response_athlete = [];
                        foreach ($data_report_elimination_individu[0] as $key => $value) {
                            $response_athlete = [
                                "type" => "individu",
                                "winner_name" => $value["athlete"],
                                "club_name" => $value["club"],
                                "rank" => $key + 1,
                                "participant_id" => $value["participant_id"],
                                "category_id" => $category_detail->id,
                                "category_label" => ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id)
                            ];
                            array_push($data_elimination_all, $response_athlete);
                        }
                    }
                }

                if (strtolower($category_team_type) == "team") {
                    $data_elimination_team = ArcheryEventParticipant::getDataEliminationTeam($category_detail->id);
                    if (!empty($data_elimination_team)) {
                        $response_tim = [];
                        foreach ($data_elimination_team as $key_r => $value) {
                            
                            $array_member = [];
                            $response_tim["winner_name"] = $value["team_name"];
                            $response_tim["participant_id"] = $value["participant_id"];
                            $response_tim["club_name"] = $value["club_name"];
                            foreach ($value["member_team"] as $key => $value_a) {
                                $participant_member = ArcheryEventParticipantMember::select("archery_event_participants.id", "archery_clubs.name")->where("archery_event_participant_members.id", $value_a["member_id"])
                                    ->join("archery_event_participants", "archery_event_participants.id", "=", "archery_event_participant_members.archery_event_participant_id")
                                    ->join("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id")
                                    ->first();
                                $res_athlete = [
                                    "name" => $value_a["name"],
                                    "participant_id" => $participant_member["id"],
                                    "member_id" => $value_a["member_id"],
                                    "club_name" => $participant_member["name"]
                                ];
                                $array_member[] = $res_athlete;
                            }
                            $response_tim["type"] = "team";
                            $response_tim["list_athlete"] = $array_member;
                            $response_tim["rank"] = $key_r + 1;
                            $response_tim["category_label"] = ArcheryEventCategoryDetail::getCategoryLabelComplete($category_detail->id);
                            $response_tim["category_id"] = $category_detail->id;
                            $data_elimination_all[] = $response_tim;
                        }
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
