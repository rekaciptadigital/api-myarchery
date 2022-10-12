<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryScoring;
use App\Models\Admin;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryQualificationSchedules;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventEliminationGroupMatch;
use App\Models\ArcheryScoringEliminationGroup;
use App\Models\UrlReport;
use Illuminate\Support\Facades\Redis;

class AddParticipantMemberScore extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $code = \explode("-", $parameters->code);
        if (count($code) < 3) {
            throw new BLoCException("kode bermasalah");
        }
        $type = $code[0];
        $session = $code[2];

        if ($type == 1 && $session == 11) {
            return $this->addScoringQualificationShotOff($parameters);
        }

        if ($type == 1) {
            return $this->addScoringQualification($parameters);
        }

        if ($type == 2) {
            if (count($code) == 5 && $code[4] == "t") {
                return $this->addScoringEliminationTeam($parameters);
            }

            if (count($code) == 4) {
                return $this->addScoringElimination($parameters);
            }
        }

        if ($type == 3) {
            return $this->addScoringQualificationSelection($parameters);
        }

        if ($type == 4)
            return $this->addScoringEliminationSelection($parameters);


        throw new BLoCException("gagal input skoring");
    }

    private function addScoringQualificationShotOff($parameters)
    {
        $admin = Admin::getProfile();
        $code = \explode("-", $parameters->code);
        if (count($code) < 3) {
            throw new BLoCException("kode bermasalah");
        }

        $type = $code[0];
        $participant_member_id = $code[1];
        $session = $code[2];

        $participant_member = ArcheryEventParticipantMember::select("archery_event_participant_members.*", "archery_event_participants.event_category_id", "archery_event_participants.event_id")
            ->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->where("archery_event_participants.status", 1)
            ->where("archery_event_participant_members.id", $participant_member_id)->first();

        if (!$participant_member) {
            throw new BLoCException("peserta tidak terdaftar");
        }

        $event = ArcheryEvent::find($participant_member->event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }


        UrlReport::removeAllUrlReport($event->id);

        $category = ArcheryEventCategoryDetail::find($participant_member->event_category_id);
        if (!$category) {
            throw new BLoCException("kategori tidak tersedia");
        }

        $data = Redis::get($category->id . "_LIVE_SCORE");
        if ($data) {
            Redis::del($category->id . "_LIVE_SCORE");
        }

        $event_elimination = ArcheryEventElimination::where("event_category_id", $category->id)->first();
        if ($event_elimination) {
            throw new BLoCException("tidak bisa input skor karena eliminasi telah ditentukan");
        }

        if ($session != 11) {
            throw new BLoCException("sesi shoot off tidak valid");
        }

        $schedule = ArcheryEventQualificationScheduleFullDay::where("participant_member_id", $participant_member_id)->first();
        if (!$schedule) {
            throw new BLoCException("jadwal belum di set");
        }
        $get_score = ArcheryScoring::where("scoring_session", $session)->where("participant_member_id", $participant_member_id)->where('type', 1)->first();
        if ($get_score && $get_score->is_lock == 1 && $admin->role->role->id != 4)
            throw new BLoCException("scoring sudah dikunci");

        $score = ArcheryScoring::makeScoringShotOffQualification($parameters->shoot_scores);

        if ($get_score) {
            $scoring = ArcheryScoring::find($get_score->id);
        } else {
            if ($participant_member->have_shoot_off === 0) {
                throw new BLoCException("tidak dapat melakukan shoot 0ff");
            }
            $scoring = new ArcheryScoring;
        }

        $scoring->participant_member_id = $participant_member_id;
        $scoring->total = $score->total;
        $scoring->total_tmp = 0;
        $scoring->scoring_session = $session;
        $scoring->type = $type;
        $scoring->item_value = "archery_event_qualification_schedule_full_day";
        $scoring->item_id = $schedule->id;
        $scoring->scoring_log = \json_encode([
            "admin" => $admin,
            "archery_event_qualification_schedule_full_day" => $schedule,
            "target_no" => $parameters->target_no
        ]);
        $scoring->scoring_detail = \json_encode($score->scors);
        if ($parameters->save_permanent) {
            $scoring->is_lock = 1;
        }

        $scoring->save();

        $member = ArcheryEventParticipantMember::find($participant_member_id);
        if (!$member) {
            throw new BLoCException("member nan");
        }
        $member->update(["have_shoot_off" => 2]);
        return $scoring;
    }

    private function addScoringElimination($parameters)
    {
        $elimination_id = $parameters->elimination_id;
        $round = $parameters->round;
        $match = $parameters->match;
        $type = $parameters->type;
        $save_permanent = $parameters->save_permanent;
        $members = $parameters->members;
        $valid = 1;
        $get_elimination = ArcheryEventElimination::find($elimination_id);
        if (!$get_elimination) {
            throw new BLoCException("elimination tidak valid");
        }

        $category = ArcheryEventCategoryDetail::find($get_elimination->event_category_id);
        if (!$category) {
            throw new BLoCException("kategori tidak tersedia");
        }

        $data = Redis::get($category->id . "_LIVE_SCORE");
        if ($data) {
            Redis::del($category->id . "_LIVE_SCORE");
        }

        $event = ArcheryEvent::find($category->event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        UrlReport::removeAllUrlReport($event->id);

        $get_member_match = ArcheryEventEliminationMatch::select(
            "archery_event_elimination_members.member_id",
            "archery_event_elimination_matches.*"
        )
            ->join("archery_event_elimination_members", "archery_event_elimination_matches.elimination_member_id", "=", "archery_event_elimination_members.id")
            ->where("archery_event_elimination_matches.event_elimination_id", $elimination_id)
            ->where("round", $round)
            ->where("match", $match)
            ->get();
        if (count($get_member_match) < 1 || count($get_member_match) > 2) {
            throw new BLoCException("match tidak valid");
        }

        foreach ($get_member_match as $key => $value) //check valid members 
        {
            if (count($get_member_match) == 2) {
                if ($value->win == 1) {
                    throw new BLoCException("match have winner");
                }

                if ($value->member_id != $members[0]["member_id"] && $value->member_id != $members[1]["member_id"]) {
                    $valid = 0;
                }
            }
        }

        if (!$valid) {
            throw new BLoCException("member tidak valid");
        }

        if ($get_elimination->elimination_scoring_type == 1) {
            if ($members[0] != [] && $members[1] == []) {
                $calculate = ArcheryScoring::calculateEliminationScoringTypePointFormatBye($members[0]);
            } elseif ($members[1] != [] && $members[0] == []) {
                $calculate = ArcheryScoring::calculateEliminationScoringTypePointFormatBye($members[1]);
            } else {
                $calculate = ArcheryScoring::calculateEliminationScoringTypePointFormat($members[0], $members[1], $save_permanent);
            }
        }

        if ($get_elimination->elimination_scoring_type == 2) {
            if ($members[0] != [] && $members[1] == []) {
                $calculate = ArcheryScoring::calculateEliminationScoringTypeTotalFormatBye($members[0]);
            } elseif ($members[1] != [] && $members[0] == []) {
                $calculate = ArcheryScoring::calculateEliminationScoringTypeTotalFormatBye($members[1]);
            } else {
                $calculate = ArcheryScoring::calculateEliminationScoringTypeTotalFormat($members[0], $members[1], $save_permanent);
            }
        }


        foreach ($get_member_match as $key => $value) //check valid members 
        {
            $participant_member_id = $value->member_id;
            $scoring = $calculate[$participant_member_id]["scores"];
            $total = $scoring["total"];
            $win = $scoring["win"];
            $result = $scoring["result"];
            $session = 1;
            $type = 2;
            $item_value = "archery_event_elimination_matches";
            $item_id = $value->id;
            $participant_scoring = ArcheryScoring::where("type", 2)->where("item_id", $item_id)->first();
            if (!$participant_scoring) {
                $participant_scoring = new ArcheryScoring;
            }

            $participant_scoring->participant_member_id = $participant_member_id;
            $participant_scoring->total = $total;
            $participant_scoring->scoring_session = $session;
            $participant_scoring->type = $type;
            $participant_scoring->item_value = $item_value;
            $participant_scoring->item_id = $item_id;
            $participant_scoring->scoring_log = \json_encode($value);
            $participant_scoring->scoring_detail = \json_encode($scoring);
            $participant_scoring->save();
            $elimination_match = ArcheryEventEliminationMatch::where("id", $value->id)->first();
            $elimination_match->result = $result;
            // if ($save_permanent == 1) {
            //     $champion = EliminationFormat::EliminationChampion($get_elimination->count_participant, $round, $match, $win);
            //     if ($champion != 0) {
            //         ArcherySeriesUserPoint::setPoint($participant_member_id, "elimination", $champion);
            //         ArcheryEventEliminationMember::where("id", $value->elimination_member_id)->update(["elimination_ranked" => $champion]);
            //     }
            //     if ($win == 1) {
            //         $elimination_match->win = $win;
            //     }
            //     $next = EliminationFormat::NextMatch($get_elimination->count_participant, $round, $match, $win);
            //     if (count($next) > 0) {
            //         ArcheryEventEliminationMatch::where("round", $next["round"])
            //             ->where("match", $next["match"])
            //             ->where("index", $next["index"])
            //             ->where("event_elimination_id", $elimination_id)
            //             ->update(["elimination_member_id" => $value->elimination_member_id]);
            //     }
            // }
            $elimination_match->save();
        }
        return true;
    }

    private function addScoringQualification($parameters)
    {
        $admin = Admin::getProfile();
        $code = \explode("-", $parameters->code);
        if (count($code) < 3) {
            throw new BLoCException("kode bermasalah");
        }

        $type = $code[0];
        $participant_member_id = $code[1];
        $session = $code[2];

        $participant_member = ArcheryEventParticipantMember::select("archery_event_participant_members.*", "archery_event_participants.event_category_id", "archery_event_participants.event_id")
            ->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->where("archery_event_participants.status", 1)
            ->where("archery_event_participant_members.id", $participant_member_id)->first();

        if (!$participant_member) {
            throw new BLoCException("peserta tidak terdaftar");
        }

        $event = ArcheryEvent::find($participant_member->event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        UrlReport::removeAllUrlReport($event->id);


        $category = ArcheryEventCategoryDetail::find($participant_member->event_category_id);
        if (!$category) {
            throw new BLoCException("kategori tidak tersedia");
        }

        $data = Redis::get($category->id . "_LIVE_SCORE");
        if ($data) {
            Redis::del($category->id . "_LIVE_SCORE");
        }

        $event_elimination = ArcheryEventElimination::where("event_category_id", $category->id)->first();
        if ($event_elimination) {
            throw new BLoCException("tidak bisa input skoring karena eliminasi telah ditentukan");
        }

        if ($category->session_in_qualification < $session)
            throw new BLoCException("sesi tidak tersedia");

        $schedule = ArcheryEventQualificationScheduleFullDay::where("participant_member_id", $participant_member_id)->first();
        if (!$schedule) {
            throw new BLoCException("jadwal belum di set");
        }
        $get_score = ArcheryScoring::where("scoring_session", $session)->where("participant_member_id", $participant_member_id)->where('type', 1)->first();
        if ($get_score && $get_score->is_lock == 1 && $admin->role->role->id != 4)
            throw new BLoCException("scoring sudah dikunci");

        $score = ArcheryScoring::makeScoring($parameters->shoot_scores);
        $event_score_id = $get_score ? $get_score->id : 0;

        if ($event_score_id) {
            $scoring = ArcheryScoring::find($event_score_id);
        } else {
            $scoring = new ArcheryScoring;
        }

        $scoring->participant_member_id = $participant_member_id;
        $scoring->total = $score->total;
        $scoring->total_tmp = $score->total_tmp_string;
        $scoring->scoring_session = $session;
        $scoring->type = $type;
        $scoring->item_value = "archery_event_qualification_schedule_full_day";
        $scoring->item_id = $schedule->id;
        $scoring->scoring_log = \json_encode([
            "admin" => $admin,
            "archery_event_qualification_schedule_full_day" => $schedule,
            "target_no" => $parameters->target_no
        ]);
        $scoring->scoring_detail = \json_encode($score->scors);
        if ($parameters->save_permanent) {
            $scoring->is_lock = 1;
        }

        $scoring->save();
        return $scoring;
    }

    private function addScoringQualificationMarathon($parameters)
    {
        $admin = Auth::user();
        $schedule_member = ArcheryQualificationSchedules::find($parameters->schedule_id);
        if ($schedule_member->is_scoring == 1)
            throw new BLoCException("scoring sudah pernah ditambahkan pada jadwal ini");

        $score = ArcheryScoring::makeScoring($parameters->shoot_scores);
        $user_scores = ArcheryScoring::where("participant_member_id", $schedule_member->participant_member_id)->get();
        $check_scoring_count = 0;
        $event_score_id = 0;
        $scoring_session = 1;
        foreach ($user_scores as $key => $value) {
            $log = \json_decode($value->scoring_log);
            if ($log->archery_qualification_schedules->id == $parameters->schedule_id) {
                $event_score_id = $value->id;
                $scoring_session = $value->scoring_session;
            } else {
                $check_scoring_count = $check_scoring_count + 1;
            }
        }

        if ($check_scoring_count > 0) {
            if ($check_scoring_count >= 3)
                throw new BLoCException("peserta sudah melakukan 3x scoring");

            if ($event_score_id == 0)
                $scoring_session = $check_scoring_count + 1;

            if ($check_scoring_count == 2) {
                $archery_event_score = ArcheryScoring::generateScoreBySession($schedule_member->participant_member_id, $parameters->type, [1, 2, 3]);
                $tmpScoring = $archery_event_score["sessions"];
                usort($tmpScoring, function ($a, $b) {
                    return $b["total_tmp"] < $a["total_tmp"] ? 1 : -1;
                });
                foreach ($tmpScoring as $key => $value) {
                    if (($scoring_session == 3 && $value["session"] != 3 && $value["total_tmp"] < $score->total_tmp)
                        || ($scoring_session < 3 && $value["session"] == 3 && $value["total_tmp"] > $score->total_tmp)
                    ) {
                        if (isset($value["scoring_id"])) { {
                                $user_score = ArcheryScoring::find($value["scoring_id"]);
                                $tmp_session = $user_score->scoring_session;
                                $user_score->scoring_session = $scoring_session;
                                $user_score->save();
                                $scoring_session = $tmp_session;
                                break;
                            }
                        }
                    }
                }
            }
        }
        if ($event_score_id) {
            $scoring = ArcheryScoring::find($event_score_id);
        } else {
            $scoring = new ArcheryScoring;
        }

        $scoring->participant_member_id = $schedule_member->participant_member_id;
        $scoring->total = $score->total;
        $scoring->total_tmp = $score->total_tmp_string;
        $scoring->scoring_session = $scoring_session;
        $scoring->type = $parameters->type;
        $scoring->item_value = "archery_qualification_schedules";
        $scoring->item_id = $schedule_member->id;
        $scoring->scoring_log = \json_encode([
            "admin" => $admin,
            "archery_qualification_schedules" => $schedule_member,
            "target_no" => $parameters->target_no
        ]);
        $scoring->scoring_detail = \json_encode($score->scors);
        $scoring->save();

        if ($parameters->save_permanent) {
            $schedule_member->is_scoring = 1;
            $schedule_member->save();
        }

        return $scoring;
    }


    private function addScoringEliminationTeam($parameters)
    {
        $elimination_group_id = $parameters->elimination_id;
        $round = $parameters->round;
        $match = $parameters->match;
        $save_permanent = $parameters->save_permanent;
        $participants = $parameters->participants;
        $valid = 1;
        $get_elimination_group = ArcheryEventEliminationGroup::find($elimination_group_id);
        if (!$get_elimination_group) {
            throw new BLoCException("elimination tidak valid");
        }

        $category = ArcheryEventCategoryDetail::find($get_elimination_group->category_id);
        if (!$category) {
            throw new BLoCException("kategori tidak tersedia");
        }

        $data = Redis::get($category->id . "_LIVE_SCORE");
        if ($data) {
            Redis::del($category->id . "_LIVE_SCORE");
        }

        $event = ArcheryEvent::find($category->event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        UrlReport::removeAllUrlReport($event->id);

        $get_participant_match = ArcheryEventEliminationGroupMatch::select(
            "archery_event_elimination_group_teams.participant_id",
            "archery_event_elimination_group_match.*"
        )
            ->join("archery_event_elimination_group_teams", "archery_event_elimination_group_match.group_team_id", "=", "archery_event_elimination_group_teams.id")
            ->where("archery_event_elimination_group_match.elimination_group_id", $elimination_group_id)
            ->where("round", $round)
            ->where("match", $match)
            ->get();

        if (count($get_participant_match) > 2 || count($get_participant_match) < 1) {
            throw new BLoCException("match tidak valid");
        }

        foreach ($get_participant_match as $key => $value) //check valid members 
        {
            if (count($get_participant_match) == 2) {
                if ($value->win == 1) {
                    throw new BLoCException("match have winner");
                }

                if ($value->participant_id != $participants[0]["participant_id"] && $value->participant_id != $participants[1]["participant_id"]) {
                    $valid = 0;
                }
            }
        }

        if (!$valid) {
            throw new BLoCException("tim tidak valid");
        }

        if ($get_elimination_group->elimination_scoring_type == 1) {
            if ($participants[0] != [] && $participants[1] == []) {
                $calculate = ArcheryScoringEliminationGroup::calculateEliminationScoringTypePointFormatbye($participants[0]);
            } elseif ($participants[1] != [] && $participants[0] == []) {
                $calculate = ArcheryScoringEliminationGroup::calculateEliminationScoringTypePointFormatbye($participants[1]);
            } else {
                $calculate = ArcheryScoringEliminationGroup::calculateEliminationScoringTypePointFormat($participants[0], $participants[1], $save_permanent);
            }
        }

        if ($get_elimination_group->elimination_scoring_type == 2) {
            if ($participants[0] != [] && $participants[1] == []) {
                $calculate = ArcheryScoringEliminationGroup::calculateEliminationScoringTypeTotalFormatBye($participants[0]);
            } elseif ($participants[1] != [] && $participants[0] == []) {
                $calculate = ArcheryScoringEliminationGroup::calculateEliminationScoringTypeTotalFormatBye($participants[1]);
            } else {
                $calculate = ArcheryScoringEliminationGroup::calculateEliminationScoringTypeTotalFormat($participants[0], $participants[1], $save_permanent);
            }
        }


        foreach ($get_participant_match as $key => $value) //check valid members 
        {
            $participant_id = $value->participant_id;
            $scoring = $calculate[$participant_id]["scores"];
            $win = $scoring["win"];
            $result = $scoring["result"];
            $elimination_group_match_id = $value->id;
            $participant_scoring = ArcheryScoringEliminationGroup::where("elimination_match_group_id", $elimination_group_match_id)->first();
            if (!$participant_scoring) {
                $participant_scoring = new ArcheryScoringEliminationGroup;
            }

            $participant_scoring->participant_id = $participant_id;
            $participant_scoring->result = $result;
            $participant_scoring->elimination_match_group_id = $elimination_group_match_id;
            $participant_scoring->scoring_log = \json_encode($value);
            $participant_scoring->scoring_detail = \json_encode($scoring);
            $participant_scoring->save();
            $elimination_group_match = ArcheryEventEliminationGroupMatch::where("id", $value->id)->first();
            $elimination_group_match->result = $result;
            // if ($save_permanent == 1) {
            //     $champion = EliminationFormat::EliminationChampion($get_elimination_group->count_participant, $round, $match, $win);
            //     if ($champion != 0) {
            //         ArcheryEventEliminationGroupTeams::where("id", $value->group_team_id)->update(["elimination_ranked" => $champion]);
            //     }
            //     if ($win == 1) {
            //         $elimination_group_match->win = $win;
            //     }
            //     $next = EliminationFormat::NextMatch($get_elimination_group->count_participant, $round, $match, $win);
            //     if (count($next) > 0) {
            //         ArcheryEventEliminationGroupMatch::where("round", $next["round"])
            //             ->where("match", $next["match"])
            //             ->where("index", $next["index"])
            //             ->where("elimination_group_id", $elimination_group_id)
            //             ->update(["group_team_id" => $value->group_team_id]);
            //     }
            // }
            $elimination_group_match->save();
        }
        return true;
    }

    private function addScoringQualificationSelection($parameters)
    {
        $admin = Admin::getProfile();
        $code = \explode("-", $parameters->code);
        if (count($code) < 3) {
            throw new BLoCException("kode bermasalah");
        }

        $type = $code[0];
        $participant_member_id = $code[1];
        $session = $code[2];

        $participant_member = ArcheryEventParticipantMember::select("archery_event_participant_members.*", "archery_event_participants.event_category_id", "archery_event_participants.event_id")
            ->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->where("archery_event_participants.status", 1)
            ->where("archery_event_participant_members.id", $participant_member_id)->first();

        if (!$participant_member) {
            throw new BLoCException("peserta tidak terdaftar");
        }

        $event = ArcheryEvent::find($participant_member->event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        UrlReport::removeAllUrlReport($event->id);


        $category = ArcheryEventCategoryDetail::find($participant_member->event_category_id);
        if (!$category) {
            throw new BLoCException("kategori tidak tersedia");
        }

        $data = Redis::get($category->id . "_LIVE_SCORE");
        if ($data) {
            Redis::del($category->id . "_LIVE_SCORE");
        }

        $event_elimination = ArcheryEventElimination::where("event_category_id", $category->id)->first();
        if ($event_elimination) {
            throw new BLoCException("tidak bisa input skoring karena eliminasi telah ditentukan");
        }

        if ($category->session_in_qualification < $session)
            throw new BLoCException("sesi tidak tersedia");

        $schedule = ArcheryEventQualificationScheduleFullDay::where("participant_member_id", $participant_member_id)->first();
        if (!$schedule) {
            throw new BLoCException("jadwal belum di set");
        }
        $get_score = ArcheryScoring::where("scoring_session", $session)->where("participant_member_id", $participant_member_id)->where('type', 3)->first();
        if ($get_score && $get_score->is_lock == 1 && $admin->role->role->id != 4)
            throw new BLoCException("scoring sudah dikunci");

        $score = ArcheryScoring::makeScoring($parameters->shoot_scores);
        $event_score_id = $get_score ? $get_score->id : 0;

        if ($event_score_id) {
            $scoring = ArcheryScoring::find($event_score_id);
        } else {
            $scoring = new ArcheryScoring;
        }

        $scoring->participant_member_id = $participant_member_id;
        $scoring->total = $score->total;
        $scoring->total_tmp = $score->total_tmp_string;
        $scoring->scoring_session = $session;
        $scoring->type = $type;
        $scoring->item_value = "archery_event_qualification_schedule_full_day";
        $scoring->item_id = $schedule->id;
        $scoring->scoring_log = \json_encode([
            "admin" => $admin,
            "archery_event_qualification_schedule_full_day" => $schedule,
            "target_no" => $parameters->target_no
        ]);
        $scoring->scoring_detail = \json_encode($score->scors);
        if ($parameters->save_permanent) {
            $scoring->is_lock = 1;
        }

        $scoring->save();
        return $scoring;
    }

    private function addScoringEliminationSelection($parameters)
    {
        $admin = Admin::getProfile();
        $code = \explode("-", $parameters->code);
        if (count($code) < 3) {
            throw new BLoCException("kode bermasalah");
        }

        $type = $code[0];
        $participant_member_id = $code[1];
        $session = $code[2];

        $participant_member = ArcheryEventParticipantMember::select("archery_event_participant_members.*", "archery_event_participants.event_category_id", "archery_event_participants.event_id")
            ->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->where("archery_event_participants.status", 1)
            ->where("archery_event_participant_members.id", $participant_member_id)->first();

        if (!$participant_member) {
            throw new BLoCException("peserta tidak terdaftar");
        }

        $event = ArcheryEvent::find($participant_member->event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        UrlReport::removeAllUrlReport($event->id);


        $category = ArcheryEventCategoryDetail::find($participant_member->event_category_id);
        if (!$category) {
            throw new BLoCException("kategori tidak tersedia");
        }

        $data = Redis::get($category->id . "_LIVE_SCORE");
        if ($data) {
            Redis::del($category->id . "_LIVE_SCORE");
        }

        // $event_elimination = ArcheryEventElimination::where("event_category_id", $category->id)->first();
        // if ($event_elimination) 
        //     throw new BLoCException("tidak bisa input skoring karena eliminasi telah ditentukan");


        if (env('COUNT_STAGE_ELIMINATION_SELECTION') < $session)
            throw new BLoCException("sesi tidak tersedia");

        $schedule = ArcheryEventQualificationScheduleFullDay::where("participant_member_id", $participant_member_id)->first();
        if (!$schedule)
            throw new BLoCException("jadwal belum di set");

        $get_score = ArcheryScoring::where("scoring_session", $session)->where("participant_member_id", $participant_member_id)->where('type', 4)->first();
        if ($get_score && $get_score->is_lock == 1 && $admin->role->role->id != 4)
            throw new BLoCException("scoring sudah dikunci");

        $score = ArcheryScoring::makeScoring($parameters->shoot_scores);
        $event_score_id = $get_score ? $get_score->id : 0;

        if ($event_score_id) {
            $scoring = ArcheryScoring::find($event_score_id);
        } else {
            $scoring = new ArcheryScoring;
        }

        $scoring->participant_member_id = $participant_member_id;
        $scoring->total = $score->total;
        $scoring->total_tmp = $score->total_tmp_string;
        $scoring->scoring_session = $session;
        $scoring->type = $type;
        $scoring->item_value = "archery_event_qualification_schedule_full_day";
        $scoring->item_id = $schedule->id;
        $scoring->scoring_log = \json_encode([
            "admin" => $admin,
            "archery_event_qualification_schedule_full_day" => $schedule,
            "target_no" => $parameters->target_no
        ]);
        $scoring->scoring_detail = \json_encode($score->scors);
        if ($parameters->save_permanent) {
            $scoring->is_lock = 1;
        }

        $scoring->save();
        return $scoring;
    }

    protected function validation($parameters)
    {
        if ($parameters->type == 1)
            return [
                'shoot_scores' => 'required',
            ];

        return [];
    }
}
