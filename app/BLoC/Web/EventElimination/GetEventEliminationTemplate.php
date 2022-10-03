<?php

namespace App\BLoC\Web\EventElimination;

use App\Models\ArcheryClub;
use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryEventEliminationSchedule;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Exceptions\BLoCException;
use App\Models\ArcheryScoring;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventEliminationGroupMatch;
use App\Models\ArcheryEventEliminationGroupMemberTeam;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryMasterTeamCategory;
use App\Models\ArcheryScoringEliminationGroup;

class GetEventEliminationTemplate extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_category_id = $parameters->get("event_category_id");

        $category = ArcheryEventCategoryDetail::find($event_category_id);
        if (!$category) {
            throw new BLoCException("category not found");
        }

        $team_category = ArcheryMasterTeamCategory::find($category->team_category_id);
        if (!$team_category) {
            throw new BLoCException("team category not found");
        }

        if (strtolower($team_category->type) == "team") {
            return $this->getTemplateTeam($category);
        }

        if (strtolower($team_category->type) == "individual") {
            return $this->getTemplateIndividu($category);
        }

        throw new BLoCException("gagal menampilkan template");
    }

    protected function validation($parameters)
    {
        return [];
    }

    public function getTemplateIndividu($category)
    {
        $elimination = ArcheryEventElimination::where("event_category_id", $category->id)->first();
        $elimination_id = 0;
        $elimination_member_count = 16;

        if ($elimination) {
            $elimination_id = $elimination->id;
            $elimination_member_count = $elimination->count_participant;
        } elseif ($category->default_elimination_count != 0) {
            $elimination_member_count = $category->default_elimination_count;
        }


        $score_type = 1; // 1 for type qualification
        $session = [];
        for ($i = 0; $i < $category->session_in_qualification; $i++) {
            $session[] = $i + 1;
        }

        $fix_members1 = ArcheryEventEliminationMatch::select(
            "archery_event_elimination_members.position_qualification",
            "users.name",
            "archery_event_participant_members.id AS member_id",
            "archery_event_participant_members.archery_event_participant_id as participant_id",
            "archery_event_participant_members.gender",
            "archery_event_elimination_matches.id",
            "archery_event_elimination_matches.round",
            "archery_event_elimination_matches.match",
            "archery_event_elimination_matches.win",
            "archery_event_elimination_matches.bud_rest",
            "archery_event_elimination_matches.target_face",
            "archery_event_elimination_matches.result"
        )
            ->leftJoin("archery_event_elimination_members", "archery_event_elimination_matches.elimination_member_id", "=", "archery_event_elimination_members.id")
            ->leftJoin("archery_event_participant_members", "archery_event_elimination_members.member_id", "=", "archery_event_participant_members.id")
            ->leftJoin("users", "users.id", "=", "archery_event_participant_members.user_id")
            ->where("archery_event_elimination_matches.event_elimination_id", $elimination_id)
            ->orderBy("archery_event_elimination_matches.round")
            ->orderBy("archery_event_elimination_matches.match")
            ->orderBy("archery_event_elimination_matches.index")
            ->get();

        // return $fix_members1;

        $qualification_rank = [];
        $updated = true;
        if ($fix_members1->count() > 0) {
            $members = [];
            foreach ($fix_members1 as $key => $value) {
                $members[$value->round][$value->match]["date"] = $value->date . " " . $value->start_time . " - " . $value->end_time;
                if ($value->member_id != null) {
                    $archery_scooring = ArcheryScoring::where("item_id", $value->id)->where("item_value", "archery_event_elimination_matches")->first();
                    $admin_total = "";
                    $is_different = 0;
                    $total_scoring = 0;

                    if ($archery_scooring) {
                        $admin_total = $archery_scooring->admin_total;
                        $scoring_detail = json_decode($archery_scooring->scoring_detail);

                        if ($admin_total != 0) {
                            $total_scoring = $admin_total;
                        } else {
                            $total_scoring = isset($scoring_detail->result) ? $scoring_detail->result : $scoring_detail->total;
                        }

                        if ($total_scoring != $admin_total) {
                            $is_different = 1;
                        }
                    }

                    $club =  ArcheryEventParticipant::select("archery_clubs.name")->join("archery_clubs", "archery_clubs.id", "=", "archery_event_participants.club_id")->where("archery_event_participants.id", $value->participant_id)->where("archery_event_participants.status", 1)->first();

                    $members[$value->round][$value->match]["teams"][] = array(
                        "id" => $value->member_id,
                        "match_id" => $value->id,
                        "name" => $value->name,
                        "gender" => $value->gender,
                        "club" =>  $club->name ?? '-',
                        "potition" => $value->position_qualification,
                        "win" => $value->win,
                        "total_scoring" => $total_scoring,
                        "status" => $value->win == 1 ? "win" : "wait",
                        "admin_total" => $admin_total,
                        "result" => $value->result,
                        "budrest_number" => $value->bud_rest != 0 ? $value->bud_rest . "" . $value->target_face : "",
                        "is_different" => $is_different,
                    );
                } else {
                    $match =  ArcheryEventEliminationMatch::where("event_elimination_id", $elimination_id)->where("round", $value->round)->where("match", $value->match)->get();
                    if ($match[0]->elimination_member_id == 0 && $match[1]->win == 1) {
                        $members[$value->round][$value->match]["teams"][] = ["status" => "bye"];
                    } elseif ($match[1]->elimination_member_id == 0 && $match[0]->win == 1) {
                        $members[$value->round][$value->match]["teams"][] = ["status" => "bye"];
                    } elseif (($match[1]->elimination_member_id == 0 && $match[0]->elimination_member_id == 0) && $value->round == 1) {
                        $members[$value->round][$value->match]["teams"][] = ["status" => "wait"];
                    } else {
                        $members[$value->round][$value->match]["teams"][] = ["status" => "wait"];
                    }
                }
            }

            $fix_members2 = $members;
            $updated = false;
            $template["rounds"] = ArcheryEventEliminationSchedule::getTemplate($fix_members2, $elimination_member_count);
        } else {
            $qualification_rank = ArcheryScoring::getScoringRankByCategoryId($category->id, $score_type, $session, false, null, true);
            $template["rounds"] = ArcheryEventEliminationSchedule::makeTemplate($qualification_rank, $elimination_member_count);
        }
        $template["updated"] = $updated;
        $template["elimination_id"] = $elimination_id;
        return $template;
    }

    public function getTemplateTeam($category_team)
    {
        $elimination = ArcheryEventEliminationGroup::where("category_id", $category_team->id)->first();
        $elimination_id = 0;
        $elimination_member_count = 16;
        if ($elimination) {
            $elimination_id = $elimination->id;
            $elimination_member_count = $elimination->count_participant;
        } elseif ($category_team->default_elimination_count != 0) {
            $elimination_member_count = $category_team->default_elimination_count;
        }

        $session = [];
        for ($i = 0; $i < $category_team->session_in_qualification; $i++) {
            $session[] = $i + 1;
        }

        $fix_teams_1 = ArcheryEventEliminationGroupMatch::select(
            "archery_event_elimination_group_teams.position",
            "archery_event_elimination_group_teams.participant_id",
            "archery_event_elimination_group_teams.team_name",
            "archery_event_elimination_group_match.id",
            "archery_event_elimination_group_match.round",
            "archery_event_elimination_group_match.match",
            "archery_event_elimination_group_match.win",
            "archery_event_elimination_group_match.bud_rest",
            "archery_event_elimination_group_match.target_face",
            "archery_event_elimination_group_match.elimination_group_id"
        )
            ->leftJoin("archery_event_elimination_group_teams", "archery_event_elimination_group_match.group_team_id", "=", "archery_event_elimination_group_teams.id")
            ->where("archery_event_elimination_group_match.elimination_group_id", $elimination_id)
            ->orderBy("archery_event_elimination_group_match.round")
            ->orderBy("archery_event_elimination_group_match.match")
            ->orderBy("archery_event_elimination_group_match.index")
            ->get();

        $lis_team = [];

        $updated = true;
        if ($fix_teams_1->count() > 0) {
            $teams = [];
            foreach ($fix_teams_1 as $key => $value) {
                $teams[$value->round][$value->match]["date"] = $value->date . " " . $value->start_time . " - " . $value->end_time;
                if ($value->participant_id != null) {
                    $archery_scooring_team = ArcheryScoringEliminationGroup::where("elimination_match_group_id", $value->id)->first();
                    $admin_total = "";
                    $is_different = 0;
                    $total_scoring = 0;
                    if ($archery_scooring_team) {
                        $admin_total = $archery_scooring_team->admin_total;
                        $scoring_detail = json_decode($archery_scooring_team->scoring_detail);

                        if ($admin_total != 0) {
                            $total_scoring = $admin_total;
                        } else {
                            $total_scoring = $scoring_detail->result;
                        }

                        if ($total_scoring != $admin_total) {
                            $is_different = 1;
                        }
                    }
                    $list_member = [];
                    $list_group_team = ArcheryEventEliminationGroupMemberTeam::where("participant_id", $value->participant_id)->get();
                    if ($list_group_team->count() > 0) {
                        foreach ($list_group_team as $gt) {
                            $m = ArcheryEventParticipantMember::select("archery_event_participant_members.user_id as user_id", "archery_event_participant_members.id as member_id", "users.name")
                                ->join("users", "users.id", "=", "archery_event_participant_members.user_id")
                                ->where("archery_event_participant_members.id", $gt->member_id)
                                ->first();

                            $list_member[] = $m;
                        }
                    }

                    $team_name = $value->team_name;

                    $teams[$value->round][$value->match]["teams"][] = array(
                        "participant_id" => $value->participant_id,
                        "match_id" => $value->id,
                        "potition" => $value->position,
                        "win" => $value->win,
                        "result" => $total_scoring,
                        "status" => $value->win == 1 ? "win" : "wait",
                        "admin_total" => $admin_total,
                        "budrest_number" => $value->bud_rest != 0 ? $value->bud_rest . "" . $value->target_face : "",
                        "is_different" => $is_different,
                        "member_team" => $list_member,
                        "team_name" => $team_name
                    );
                } else {
                    $match = ArcheryEventEliminationGroupMatch::where("elimination_group_id", $elimination_id)->where("round", $value->round)->where("match", $value->match)->get();
                    if ($match[0]->group_team_id == 0 && $match[1]->win == 1) {
                        $teams[$value->round][$value->match]["teams"][] = ["status" => "bye"];
                    } elseif ($match[1]->group_team_id == 0 && $match[0]->win == 1) {
                        $teams[$value->round][$value->match]["teams"][] = ["status" => "bye"];
                    } elseif (($match[0]->group_team_id == 0 && $match[1]->group_team_id == 0) && $value->round == 1) {
                        $teams[$value->round][$value->match]["teams"][] = ["status" => "bye"];
                    } else {
                        $teams[$value->round][$value->match]["teams"][] = ["status" => "wait"];
                    }
                }
            }

            $fix_team_2 = $teams;
            $updated = false;
            $template["rounds"] = ArcheryEventEliminationSchedule::getTemplate($fix_team_2, $elimination_member_count);
        } else {
            if ($category_team->team_category_id == "mix_team") {
                $lis_team = ArcheryScoring::mixTeamBestOfThree($category_team);
            } else {
                $team_cat = ($category_team->team_category_id) == "male_team" ? "individu male" : "individu female";
                $category_detail_individu = ArcheryEventCategoryDetail::where("event_id", $category_team->event_id)
                    ->where("age_category_id", $category_team->age_category_id)
                    ->where("competition_category_id", $category_team->competition_category_id)
                    ->where("distance_id", $category_team->distance_id)
                    ->where("team_category_id", $team_cat)
                    ->first();

                if (!$category_detail_individu) {
                    throw new BLoCException("category individu tidak ditemukan");
                }

                $lis_team = ArcheryScoring::teamBestOfThree($category_detail_individu->id, $category_detail_individu->session_in_qualification, $category_team->id);
            }
            $template["rounds"] = ArcheryEventEliminationSchedule::makeTemplateTeam($lis_team, $elimination_member_count);
        }
        $template["updated"] = $updated;
        $template["elimination_group_id"] = $elimination_id;
        return $template;
    }
}
