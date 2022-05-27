<?php

namespace App\BLoC\Web\EventElimination;

use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryEventEliminationSchedule;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Exceptions\BLoCException;
use App\Models\ArcheryScoring;
use App\Models\ArcheryEventElimination;

class GetEventEliminationTemplate extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $elimination_member_count = $parameters->get('elimination_member_count');
        $event_category_id = $parameters->get("event_category_id");

        $elimination = ArcheryEventElimination::where("event_category_id", $event_category_id)->first();
        $elimination_id = 0;
        if ($elimination) {
            $elimination_member_count = $elimination->count_participant;
            $elimination_id = $elimination->id;
        }

        $category = ArcheryEventCategoryDetail::find($event_category_id);
        if (!$category) {
            throw new BLoCException("category not found");
        }
        $score_type = 1; // 1 for type qualification
        $session = [];
        for ($i = 0; $i < $category->session_in_qualification; $i++) {
            $session[] = $i + 1;
        }

        $fix_members = ArcheryEventEliminationMatch::select(
            "archery_event_elimination_members.position_qualification",
            "users.name",
            "archery_event_participant_members.id AS member_id",
            "archery_event_participant_members.club",
            "archery_event_participant_members.gender",
            "archery_event_elimination_matches.id",
            "archery_event_elimination_matches.result",
            "archery_event_elimination_matches.round",
            "archery_event_elimination_matches.match",
            "archery_event_elimination_matches.win",
            "archery_event_elimination_schedules.date",
            "archery_event_elimination_schedules.start_time",
            "archery_event_elimination_schedules.end_time"
        )
            ->leftJoin("archery_event_elimination_members", "archery_event_elimination_matches.elimination_member_id", "=", "archery_event_elimination_members.id")
            ->leftJoin("archery_event_participant_members", "archery_event_elimination_members.member_id", "=", "archery_event_participant_members.id")
            ->leftJoin("users", "users.id", "=", "archery_event_participant_members.user_id")
            ->leftJoin("archery_event_elimination_schedules", "archery_event_elimination_matches.elimination_schedule_id", "=", "archery_event_elimination_schedules.id")
            ->where("archery_event_elimination_matches.event_elimination_id", $elimination_id)
            ->get();
        $qualification_rank = [];
        $updated = true;
        if ($fix_members->count() > 0) {
            $members = [];
            foreach ($fix_members as $key => $value) {
                $members[$value->round][$value->match]["date"] = $value->date . " " . $value->start_time . " - " . $value->end_time;
                if ($value->name != null) {
                    $archery_scooring = ArcheryScoring::where("participant_member_id", $value->member_id)->where("type", 2)->first();
                    $admin_total = 0;
                    if ($archery_scooring) {
                        $admin_total = $archery_scooring->admin_total;
                    }
                    $members[$value->round][$value->match]["teams"][] = array(
                        "id" => $value->member_id,
                        "name" => $value->name,
                        "gender" => $value->gender,
                        "club" => $value->club,
                        "potition" => $value->position_qualification,
                        "win" => $value->win,
                        "result" => $value->result,
                        "status" => $value->win == 1 ? "win" : "wait",
                        "admin_total" => $admin_total
                    );
                } else {
                    $members[$value->round][$value->match]["teams"][] = ["status" => "bye"];
                }
            }

            $fix_members = $members;
            $updated = false;
            $template["rounds"] = ArcheryEventEliminationSchedule::getTemplate($fix_members, $elimination_member_count);
        } else {
            $qualification_rank = ArcheryScoring::getScoringRankByCategoryId($event_category_id, $score_type, $session);
            $template["rounds"] = ArcheryEventEliminationSchedule::makeTemplate($qualification_rank, $elimination_member_count);
        }
        // $template["rounds"] = ArcheryEventEliminationSchedule::makeTemplate2($qualification_rank, $elimination_member_count, $match_type, $event_category_id, $gender, $fix_members);
        $template["updated"] = $updated;
        $template["elimination_id"] = $elimination_id;
        return $template;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
