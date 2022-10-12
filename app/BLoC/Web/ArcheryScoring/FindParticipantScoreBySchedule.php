<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryClub;
use App\Models\ArcheryScoring;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventEliminationGroupMatch;
use App\Models\ArcheryEventEliminationGroupMemberTeam;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryQualificationSchedules;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryScoringEliminationGroup;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Abstracts\Retrieval;

class FindParticipantScoreBySchedule extends Retrieval
{
    public function getDescription()
    {
        return "
            param code value :
            1. for fullday qualification make code : <type scoring qualification>-<member_id>-<session> ex (1-23-1) 
            2. for fullday qualification by budrest make code : <type scoring qualification>-<category_id>-<session>-<budrest_number> ex (1-23-1-2) 
        ";
    }

    protected function process($parameters)
    {
        $code = $parameters->get("code");

        $array_code = explode("-", $code);
        $type_code = $array_code[0];
        if ($type_code == 1) {
            if (isset($array_code[3])) {
                return $this->qualificationFullDayByBudrest($parameters);
            }
            $session = $array_code[2];
            if ($session == 11) {
                return $this->shootOffQualification($parameters);
            }
            return $this->qualificationFullDay($parameters);
        } elseif ($type_code == 2) {
            $elimination_id = $array_code[1];
            $match = $array_code[2];
            $round = $array_code[3];
            if (count($array_code) == 5) {
                return $this->eliminationTeam($elimination_id, $match, $round);
            }
            return $this->elimination($elimination_id, $match, $round);
        } elseif ($type_code == 3) {
            if (isset($array_code[3])) {
                return $this->qualificationFullDayByBudrest($parameters);
            }
            $session = $array_code[2];
            if ($session == 11) {
                return $this->shootOffQualification($parameters);
            }
            return $this->qualificationFullDay($parameters);
        } elseif ($type_code == 4) {
            return $this->eliminationSelection($parameters);
        }
        throw new BLoCException("gagal find score");
    }

    private function qualificationFullDayByBudrest($parameters)
    {
        $code = explode("-", $parameters->code);
        $type = $code[0];
        $category_id = $code[1];
        $session = $code[2];
        $budrest = $code[3];

        $participant_members_schedules = ArcheryEventQualificationScheduleFullDay::select("archery_event_qualification_schedule_full_day.*")
            ->join("archery_event_qualification_time", "archery_event_qualification_schedule_full_day.qalification_time_id", "=", "archery_event_qualification_time.id")
            ->where("archery_event_qualification_time.category_detail_id", $category_id)
            ->where("archery_event_qualification_schedule_full_day.bud_rest_number", $budrest)->get();

        $response = [];
        foreach ($participant_members_schedules as $key => $value) {
            $participant_member_id = $value->participant_member_id;
            $participant_member = ArcheryEventParticipantMember::select("archery_event_participant_members.*", "archery_event_participants.event_category_id")
                ->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
                ->where("archery_event_participants.status", 1)
                ->where("archery_event_participant_members.id", $participant_member_id)->first();
            if (!$participant_member)
                throw new BLoCException("member tidak ditemukan");

            $score = ArcheryScoring::where("participant_member_id", $participant_member_id)
                ->where("scoring_session", $session)
                ->where("type", $type)
                ->first();
            $output = (object)array();
            $category_detail = ArcheryEventCategoryDetail::find($participant_member->event_category_id);
            if (!$category_detail) {
                throw new BLoCException("kategori tidak ditemukan");
            }
            $s = isset($score->scoring_detail) ? ArcheryScoring::makeScoringFormat(\json_decode($score->scoring_detail), null, $category_detail->count_stage, $category_detail->count_shot_in_stage) : ArcheryScoring::makeScoringFormat((object) array(), null, $category_detail->count_stage, $category_detail->count_shot_in_stage);
            $output->participant = ArcheryEventParticipantMember::memberDetail($participant_member_id);
            $output->score = $s;
            $output->category = $category_detail->getCategoryDetailById($category_detail->id);
            $schedule = $value;
            $output->budrest_number = $schedule && !empty($schedule->bud_rest_number) ? $schedule->bud_rest_number . $schedule->target_face : "";
            $output->session = $session;
            $output->schedule_id = $value->id;
            $output->is_updated = 1;
            if (isset($score->is_lock))
                $output->is_updated = $score->is_lock == 1 ? 0 : 1;

            $response[] = $output;
        }
        return $response;
    }

    private function shootOffQualification($parameters)
    {
        $code = explode("-", $parameters->code);
        $type = $code[0];
        $participant_member_id = $code[1];
        $session = $code[2];
        $participant_member = ArcheryEventParticipantMember::select("archery_event_participant_members.*", "archery_event_participants.event_category_id")
            ->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->where("archery_event_participants.status", 1)
            ->where("archery_event_participant_members.id", $participant_member_id)->first();
        if (!$participant_member)
            throw new BLoCException("member tidak ditemukan");

        $score = ArcheryScoring::where("participant_member_id", $participant_member_id)
            ->where("scoring_session", $session)
            ->where("type", $type)
            ->first();
        $output = (object)array();

        $output->participant = ArcheryEventParticipantMember::memberDetail($participant_member_id);
        $category_detail = ArcheryEventCategoryDetail::find($participant_member->event_category_id);
        if (!$category_detail) {
            throw new BLoCException("kategori tidak ditemukan");
        }
        $s = isset($score) ? ArcheryScoring::makeScoringFormat((object)\json_decode($score->scoring_detail), $session, $category_detail->count_stage, $category_detail->count_shot_in_stage) : ArcheryScoring::makeScoringFormat((object) array(), $session, $category_detail->count_stage, $category_detail->count_shot_in_stage);
        $output->score = $s;
        $output->category = $category_detail->getCategoryDetailById($category_detail->id);
        $schedule = ArcheryEventQualificationScheduleFullDay::where("participant_member_id", $participant_member_id)->first();
        $output->budrest_number = $schedule && !empty($schedule->bud_rest_number) ? $schedule->bud_rest_number . $schedule->target_face : "";
        $output->session = $session;
        $output->is_updated = 1;
        if (isset($score->is_lock))
            $output->is_updated = $score->is_lock == 1 ? 0 : 1;
        return $output;
    }

    private function qualificationFullDay($parameters)
    {
        $code = explode("-", $parameters->code);
        $type = $code[0];
        $participant_member_id = $code[1];
        $session = $code[2];
        $participant_member = ArcheryEventParticipantMember::select("archery_event_participant_members.*", "archery_event_participants.event_category_id")
            ->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->where("archery_event_participants.status", 1)
            ->where("archery_event_participant_members.id", $participant_member_id)->first();
        if (!$participant_member)
            throw new BLoCException("member tidak ditemukan");

        $score = ArcheryScoring::where("participant_member_id", $participant_member_id)
            ->where("scoring_session", $session)
            ->where("type", $type)
            ->first();
        $output = (object)array();
        $output->participant = ArcheryEventParticipantMember::memberDetail($participant_member_id);
        $category_detail = ArcheryEventCategoryDetail::find($participant_member->event_category_id);
        if (!$category_detail) {
            throw new BLoCException("kategori tidak ditemukan");
        }
        $s = isset($score->scoring_detail) ? ArcheryScoring::makeScoringFormat(\json_decode($score->scoring_detail), null, $category_detail->count_stage, $category_detail->count_shot_in_stage) : ArcheryScoring::makeScoringFormat((object) array(), null, $category_detail->count_stage, $category_detail->count_shot_in_stage);
        $output->score = $s;
        $output->category = $category_detail->getCategoryDetailById($category_detail->id);
        $schedule = ArcheryEventQualificationScheduleFullDay::where("participant_member_id", $participant_member_id)->first();
        $output->budrest_number = $schedule && !empty($schedule->bud_rest_number) ? $schedule->bud_rest_number . $schedule->target_face : "";
        $output->session = $session;
        $output->schedule_id = $schedule->id;
        $output->is_updated = 1;
        if (isset($score->is_lock))
            $output->is_updated = $score->is_lock == 1 ? 0 : 1;
        return $output;
    }

    private function qualification($parameters)
    {
        $schedule_member = ArcheryQualificationSchedules::find($parameters->schedule_id);
        $user_scores = ArcheryScoring::where("participant_member_id", $schedule_member->participant_member_id)->get();
        $session = count($user_scores) + 1;
        $score = (object)array();
        foreach ($user_scores as $key => $value) {
            $log = \json_decode($value->scoring_log);
            if (isset($log->archery_qualification_schedules) && $log->archery_qualification_schedules->id == $parameters->schedule_id) {
                $score = $value;
                $session = $value->scoring_session;
            }
        }
        $output = (object)array();
        $s = isset($score->scoring_detail) ? ArcheryScoring::makeScoringFormat(\json_decode($score->scoring_detail)) : ArcheryScoring::makeScoringFormat((object) array());
        $output->participant = ArcheryEventParticipantMember::memberDetail($schedule_member->participant_member_id);
        $output->score = $s;
        $output->session = $session;
        $output->is_updated = $schedule_member->is_scoring == 1 ? 0 : 1;
        return $output;
    }

    private function elimination($elimination_id, $match, $round)
    {
        $elimination = ArcheryEventElimination::find($elimination_id);
        if (!$elimination) {
            throw new BLoCException("elimination belum di set");
        }

        if ($elimination->elimination_scoring_type == 0) {
            throw new BLoCException("elimination scooring type belum ditentukan");
        }

        $scores = [];

        $members = ArcheryEventEliminationMatch::select(
            "archery_event_elimination_members.member_id",
            "archery_event_elimination_matches.*"
        )
            ->join("archery_event_elimination_members", "archery_event_elimination_matches.elimination_member_id", "=", "archery_event_elimination_members.id")
            ->where("archery_event_elimination_matches.match", $match)
            ->where("archery_event_elimination_matches.round", $round)
            ->where("archery_event_elimination_matches.event_elimination_id", $elimination_id)
            ->get();

        foreach ($members as $key => $value) {
            $output = (object)array();
            $member_score = ArcheryScoring::where("item_value", "archery_event_elimination_matches")
                ->where("item_id", $value->id)
                ->where("participant_member_id", $value->member_id)
                ->first();
            $admin_total = 0;
            if (!$member_score) {
                if ($elimination->elimination_scoring_type == 1) {
                    $s = ArcheryScoring::makeEliminationScoringTypePointFormat();
                    $s['admin_total'] = $admin_total;
                }

                if ($elimination->elimination_scoring_type == 2) {
                    $s = ArcheryScoring::makeEliminationScoringTypeTotalFormat();
                    $s['admin_total'] = $admin_total;
                }
            } else {
                $s = \json_decode($member_score->scoring_detail);
                $s->admin_total = $member_score->admin_total;
                $s->is_different = $member_score->admin_total != $member_score->total ? 1 : 0;
            }

            $output->participant = ArcheryEventParticipantMember::memberDetail($value->member_id);
            $output->scores = $s;
            $output->round = $round;
            $output->is_updated = 1;
            $output->budrest_number = $value->bud_rest != 0 ? $value->bud_rest . $value->target_fave : "";
            $scores[] = $output;
        }

        if (count($scores) == 1) {
            if ($members[0]->index == 0) {
                array_push($scores, []);
            } else {
                array_unshift($scores, []);
            }
        }

        return $scores;
    }

    protected function validation($parameters)
    {
        return [
            "code" => "required"
        ];
    }

    private function eliminationTeam($elimination_id, $match, $round)
    {
        $elimination = ArcheryEventEliminationGroup::find($elimination_id);
        if (!$elimination) {
            throw new BLoCException("data elimination tidak ditemukan");
        }

        $category = ArcheryEventCategoryDetail::find($elimination->category_id);
        if (!$category) {
            throw new BLoCException("category not found");
        }

        if ($elimination->elimination_scoring_type == 0) {
            throw new BLoCException("elimination scooring type belum ditentukan");
        }

        $scores = [];

        $get_participant_match = ArcheryEventEliminationGroupMatch::select(
            "archery_event_elimination_group_teams.participant_id",
            "archery_event_elimination_group_teams.team_name",
            "archery_event_elimination_group_match.*"
        )
            ->join("archery_event_elimination_group_teams", "archery_event_elimination_group_match.group_team_id", "=", "archery_event_elimination_group_teams.id")
            ->where("archery_event_elimination_group_match.elimination_group_id", $elimination_id)
            ->where("round", $round)
            ->where("match", $match)
            ->get();

        if ($category->team_category_id == "mix_team") {
            $lis_team = ArcheryScoring::mixTeamBestOfThree($category);
        } else {
            $team_cat = ($category->team_category_id) == "male_team" ? "individu male" : "individu female";
            $category_detail_individu = ArcheryEventCategoryDetail::where("event_id", $category->event_id)
                ->where("age_category_id", $category->age_category_id)
                ->where("competition_category_id", $category->competition_category_id)
                ->where("distance_id", $category->distance_id)
                ->where("team_category_id", $team_cat)
                ->first();

            if (!$category_detail_individu) {
                throw new BLoCException("category individu tidak ditemukan");
            }

            $lis_team = ArcheryScoring::teamBestOfThree($category_detail_individu->id, $category_detail_individu->session_in_qualification, $category->id);
        }

        foreach ($get_participant_match as $key => $value) {
            $output = (object)array();
            $participant_scoring = ArcheryScoringEliminationGroup::where("elimination_match_group_id", $value->id)->first();
            $admin_total = 0;
            $list_member = [];
            $team_detail = [];
            $participant_detail = ArcheryEventParticipant::find($value->participant_id);
            if (!$participant_detail) {
                throw new BLoCException("participant not found");
            }
            $team_name = $value->team_name;
            $team_detail["participant_id"] = $participant_detail->id;
            $team_detail["team_name"] = $team_name;
            $team_detail["club"] = ArcheryClub::find($participant_detail->club_id);
            // dapatkan member team
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
            if (!$participant_scoring) {
                if ($elimination->elimination_scoring_type == 1) {
                    $s = ArcheryScoringEliminationGroup::makeEliminationScoringTypePointFormat();
                    $s['admin_total'] = $admin_total;
                }

                if ($elimination->elimination_scoring_type == 2) {
                    $s = ArcheryScoringEliminationGroup::makeEliminationScoringTypeTotalFormat();
                    $s['admin_total'] = $admin_total;
                }
            } else {
                $s = \json_decode($participant_scoring->scoring_detail);
                $s->admin_total = $participant_scoring->admin_total;
                $s->is_different = $participant_scoring->admin_total != $participant_scoring->result ? 1 : 0;
            }
            $output->team_detail = $team_detail;
            $output->list_member = $list_member;
            $output->scores = $s;
            $output->round = $round;
            $output->is_updated = 1;
            $category_response = [];
            if ($category) {
                $category_response["id"] = $category->id;
                $category_response["age_category_id"] = $category->age_category_id;
                $category_response["team_category_id"] = $category->team_category_id;
                $category_response["competition_category_id"] = $category->competition_category_id;
                $category_response["distance_id"] = $category->distance_id;
            }
            $output->category = $category_response;
            $output->budrest_number = $value->bud_rest != 0 && $value->target_face != "" ? $value->bud_rest . $value->target_face : "";
            $scores[] = $output;
        }

        if (count($scores) == 1) {
            if ($get_participant_match[0]->index == 0) {
                array_push($scores, []);
            } else {
                array_unshift($scores, []);
            }
        }

        return $scores;
    }

    private function eliminationSelection($parameters)
    {
        $code = explode("-", $parameters->code);
        $type = $code[0];
        $participant_member_id = $code[1];
        $session = $code[2];
        $participant_member = ArcheryEventParticipantMember::select("archery_event_participant_members.*", "archery_event_participants.event_category_id")
            ->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->where("archery_event_participants.status", 1)
            ->where("archery_event_participant_members.id", $participant_member_id)->first();
        if (!$participant_member)
            throw new BLoCException("member tidak ditemukan");

        $score = ArcheryScoring::where("participant_member_id", $participant_member_id)
            ->where("scoring_session", $session)
            ->where("type", $type)
            ->first();
        $output = (object)array();
        $output->participant = ArcheryEventParticipantMember::memberDetail($participant_member_id);
        $category_detail = ArcheryEventCategoryDetail::find($participant_member->event_category_id);
        if (!$category_detail) {
            throw new BLoCException("kategori tidak ditemukan");
        }
        $s = isset($score->scoring_detail) ? ArcheryScoring::makeScoringFormat(\json_decode($score->scoring_detail), null, env('COUNT_STAGE_ELIMINATION_SELECTION'), env('COUNT_SHOT_IN_STAGE_ELIMINATION_SELECTION')) : ArcheryScoring::makeScoringFormat((object) array(), null, env('COUNT_STAGE_ELIMINATION_SELECTION'), env('COUNT_SHOT_IN_STAGE_ELIMINATION_SELECTION'));
        $output->score = $s;
        $output->category = $category_detail->getCategoryDetailById($category_detail->id);
        $schedule = ArcheryEventQualificationScheduleFullDay::where("participant_member_id", $participant_member_id)->first();
        $output->budrest_number = $schedule && !empty($schedule->bud_rest_number) ? $schedule->bud_rest_number . $schedule->target_face : "";
        $output->session = $session;
        $output->schedule_id = $schedule->id;
        $output->is_updated = 1;
        if (isset($score->is_lock))
            $output->is_updated = $score->is_lock == 1 ? 0 : 1;
        return $output;
    }
}
