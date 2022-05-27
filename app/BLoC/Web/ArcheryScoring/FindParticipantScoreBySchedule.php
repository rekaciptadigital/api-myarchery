<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryScoring;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryQualificationSchedules;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\ArcheryEventParticipantMember;
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
            return $this->elimination($elimination_id, $match, $round);
        }
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
            $s = isset($score->scoring_detail) ? ArcheryScoring::makeScoringFormat(\json_decode($score->scoring_detail)) : ArcheryScoring::makeScoringFormat((object) array());
            $output->participant = ArcheryEventParticipantMember::memberDetail($participant_member_id);
            $output->score = $s;
            $category_detail = ArcheryEventCategoryDetail::find($participant_member->event_category_id);
            if (!$category_detail) {
                throw new BLoCException("kategori tidak ditemukan");
            }
            $output->category = $category_detail->getCategoryDetailById($category_detail->id);
            $schedule = $value;
            $output->budrest_number = $schedule && !empty($schedule->bud_rest_number) ? $schedule->bud_rest_number . $schedule->target_face : "";
            $output->session = $session;
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

        $s = isset($score) ? ArcheryScoring::makeScoringFormat((object)\json_decode($score->scoring_detail), $session) : ArcheryScoring::makeScoringFormat((object) array(), $session);
        $output->participant = ArcheryEventParticipantMember::memberDetail($participant_member_id);
        $output->score = $s;
        $category_detail = ArcheryEventCategoryDetail::find($participant_member->event_category_id);
        if (!$category_detail) {
            throw new BLoCException("kategori tidak ditemukan");
        }
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
        $s = isset($score->scoring_detail) ? ArcheryScoring::makeScoringFormat(\json_decode($score->scoring_detail)) : ArcheryScoring::makeScoringFormat((object) array());
        $output->participant = ArcheryEventParticipantMember::memberDetail($participant_member_id);
        $output->score = $s;
        $category_detail = ArcheryEventCategoryDetail::find($participant_member->event_category_id);
        if (!$category_detail) {
            throw new BLoCException("kategori tidak ditemukan");
        }
        $output->category = $category_detail->getCategoryDetailById($category_detail->id);
        $schedule = ArcheryEventQualificationScheduleFullDay::where("participant_member_id", $participant_member_id)->first();
        $output->budrest_number = $schedule && !empty($schedule->bud_rest_number) ? $schedule->bud_rest_number . $schedule->target_face : "";
        $output->session = $session;
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
                $s->$member_score->admin_total;
            }

            $output->participant = ArcheryEventParticipantMember::memberDetail($value->member_id);
            $output->scores = $s;
            $output->round = $round;
            $output->is_updated = 1;
            $scores[] = $output;
        }

        return $scores;
    }

    protected function validation($parameters)
    {
        return [
            "code" => "required"
        ];
    }
}
