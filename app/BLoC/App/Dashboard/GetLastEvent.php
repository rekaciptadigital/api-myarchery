<?php

namespace App\BLoC\App\Dashboard;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryScoring;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;

class GetLastEvent extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = Auth::guard('app-api')->user();
        $archery_event = ArcheryEvent::select("archery_events.*")
            ->join("archery_event_participants", "archery_event_participants.event_id", "=", "archery_events.id")
            ->where("archery_events.event_start_datetime", "<", date("Y-m-d H:i:s", time()))
            ->where("archery_event_participants.user_id", $user->id)
            ->where("archery_event_participants.status", 1)
            ->orderBy("archery_events.event_start_datetime", "desc")
            ->first();

        $archery_event_participants = ArcheryEventParticipant::select(
            "archery_master_competition_categories.label as competition",
            "archery_master_age_categories.label as age",
            "archery_master_distances.label as distance",
            "archery_master_team_categories.label as team",
            "archery_master_team_categories.type as type_team",
            "archery_event_qualification_schedule_full_day.bud_rest_number",
            "archery_event_qualification_schedule_full_day.target_face",
            "archery_event_category_details.session_in_qualification",
            "archery_event_participant_members.id as member_id",
            "archery_event_participants.event_category_id as category_id"
        )
            ->join("archery_master_competition_categories", "archery_master_competition_categories.id", "=", "archery_event_participants.competition_category_id")
            ->join("archery_master_age_categories", "archery_master_age_categories.id", "=", "archery_event_participants.age_category_id")
            ->join("archery_master_distances", "archery_master_distances.id", "=", "archery_event_participants.distance_id")
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_participants.team_category_id")
            ->join("archery_event_category_details", "archery_event_category_details.id", "=", "archery_event_participants.event_category_id")
            ->join("archery_event_participant_members", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->join("archery_event_qualification_schedule_full_day", "archery_event_qualification_schedule_full_day.participant_member_id", "=", "archery_event_participant_members.id")
            ->where("archery_event_participants.event_id", $archery_event->id)
            ->where("archery_event_participants.status", 1)
            ->where("archery_event_participants.user_id", $user->id)
            ->orderBy("archery_master_competition_categories.label")
            ->get();

        $data = null;

        if ($archery_event_participants->count() > 0) {
            $data = (object)[];
            $data->detail_event = (object)[
                "id" => $archery_event->id,
                "name" => $archery_event->event_name
            ];

            foreach ($archery_event_participants as $value_archery_event_participants) {
                $category_id = $value_archery_event_participants->category_id;
                $archery_event_category_detail = ArcheryEventCategoryDetail::find($category_id);
                if (!$archery_event_category_detail || $value_archery_event_participants->type_team != "Individual") {
                    continue;
                }
                $array_sessions = $archery_event_category_detail->getArraySessionCategory();
                $qualification_score_all_member = ArcheryScoring::getScoringRankByCategoryId($category_id, 1, $array_sessions, false, null, false, 1);
                // return $qualification_score_all_member;
                $rank = 0;
                $sessions = [];
                foreach ($qualification_score_all_member as $key_qualification_score_all_member => $value_qualification_score_all_member) {
                    if ($value_qualification_score_all_member["member"]["id"] == $value_archery_event_participants->member_id) {
                        $rank = $value_qualification_score_all_member["total"] == 0 ? "" : $value_qualification_score_all_member["rank"];
                        for ($s = 1; $s <= $value_archery_event_participants->session_in_qualification; $s++) {
                            $sessions[] = (object)[
                                "session_name" => "sesi " . $s,
                                "count_x" => $value_qualification_score_all_member["sessions"][$s]["total_x"],
                                "count_ten" => $value_qualification_score_all_member["sessions"][$s]["total_ten"],
                                "count_number" => $value_qualification_score_all_member["sessions"][$s]["total_one_to_nine"],
                                "count_total_arrow" => $archery_event_category_detail->count_stage * $archery_event_category_detail->count_shot_in_stage,
                                "count_x_plus_ten" => $value_qualification_score_all_member["sessions"][$s]["total_x_plus_ten"],
                                "total_score" => $value_qualification_score_all_member["sessions"][$s]["total"],
                            ];
                        }
                        $sessions[] = (object)[
                            "session_name" => "total skor",
                            "count_x" => $value_qualification_score_all_member["total_x"],
                            "count_ten" => $value_qualification_score_all_member["total_ten"],
                            "count_number" => $value_qualification_score_all_member["total_one_to_nine"],
                            "count_total_arrow" => $archery_event_category_detail->count_stage * $archery_event_category_detail->count_shot_in_stage * $archery_event_category_detail->session_in_qualification,
                            "count_x_plus_ten" => $value_qualification_score_all_member["total_x_plus_ten"],
                            "total_score" => $value_qualification_score_all_member["total"],
                        ];
                    }
                }
                $data->list_category[] = (object)[
                    "competition" => $value_archery_event_participants->competition,
                    "age" => $value_archery_event_participants->age,
                    "distance" => $value_archery_event_participants->distance,
                    "team" => $value_archery_event_participants->team,
                    "rank" => $rank,
                    "sessions" => $sessions
                ];
            }
        }

        return $data;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
