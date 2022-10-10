<?php

namespace App\BLoC\Web\EventElimination;

use App\Models\ArcheryScoring;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationSchedule;
use DAI\Utils\Abstracts\Transactional;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventEliminationGroupMatch;
use App\Models\ArcheryEventEliminationGroupMemberTeam;
use App\Models\ArcheryEventEliminationGroupTeams;
use DAI\Utils\Exceptions\BLoCException;
use App\Models\ArcheryEventEliminationMember;
use App\Models\ArcherySeriesUserPoint;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventMasterCompetitionCategory;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryMasterTeamCategory;
use App\Models\UrlReport;
use Illuminate\Support\Facades\Redis;

class SetEventEliminationV2 extends Transactional
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
            throw new BLoCException("kategori tidak ada");
        }

        UrlReport::removeAllUrlReport($category->event_id);

        $data = Redis::get($category->id . "_LIVE_SCORE");
        if ($data) {
            Redis::del($category->id . "_LIVE_SCORE");
        }


        $team_category = ArcheryMasterTeamCategory::find($category->team_category_id);
        if (!$team_category) {
            throw new BLoCException("team category not found");
        }

        $score_type = 1; // 1 for type qualification


        $competition_category = ArcheryEventMasterCompetitionCategory::find($category->competition_category_id);
        if (!$competition_category) {
            throw new BLoCException("COMPETITION NAN");
        }

        if ($competition_category->scooring_accumulation_type == 0) {
            throw new BLoCException("tipe scooring kategori belum ditentukan");
        }

        $match_type = $parameters->match_type;
        $scoring_type = $competition_category->scooring_accumulation_type; // 1 for point, 2 for acumalition score
        $elimination_member_count = $category->default_elimination_count;
        if ($elimination_member_count === 0) {
            throw new BLoCException("jumlah peserta elimination belum ditentukan");
        }


        $session = [];
        for ($i = 0; $i < $category->session_in_qualification; $i++) {
            $session[] = $i + 1;
        }


        if (strtolower($team_category->type) == "team") {
            return $this->makeTemplateTeam($team_category->id, $category, $elimination_member_count, $scoring_type);
        }

        if (strtolower($team_category->type) == "individual") {
            return $this->makeTemplateIndividu($event_category_id, $score_type, $session, $elimination_member_count, $match_type, $scoring_type);
        }

        throw new BLoCException("gagal membuat template");
    }

    protected function validation($parameters)
    {
        return [
            'match_type' => 'required',
            'event_category_id' => 'required|exists:archery_event_category_details,id',
        ];
    }

    private function makeTemplateIndividu($category_id, $score_type, $session, $elimination_member_count, $match_type, $type_scoring)
    {
        $qualification_rank = ArcheryScoring::getScoringRankByCategoryId($category_id, $score_type, $session, false, null, true);

        // cek apakah terdapat peserta yang belum melakukan shoot qualifikasi
        if (count($qualification_rank) > 0) {
            foreach ($qualification_rank as $key => $value) {
                // if ($value["total"] == 0) {
                //     throw new BLoCException("skor kualifikasi masih kosong");
                // }

                foreach ($session as $key => $s) {
                    $scoring_per_session =  ArcheryScoring::where("participant_member_id", $value["member"]->id)
                        ->where("type", 1)
                        ->where("scoring_session", $s)
                        ->first();
                    if (!$scoring_per_session) {
                        // throw new BLoCException("terdapat peserta yang belum melakukan shoot kualifikasi secara lengkap");
                    }
                }
            }
        }

        // cek apakah ada yang telah melakukan shoot di eliminasi
        $participant_collection_have_shoot_off = ArcheryScoring::select(
            "archery_event_participant_members.*",
        )
            ->join("archery_event_participant_members", "archery_event_participant_members.id", "=", "archery_scorings.participant_member_id")
            ->join("archery_event_participants", "archery_event_participants.id", "=", "archery_event_participant_members.archery_event_participant_id")
            ->where('archery_event_participants.status', 1)
            ->where('archery_event_participants.event_category_id', $category_id)
            ->where("archery_event_participant_members.have_shoot_off", 1)
            ->distinct()
            ->get();

        $participant_collection_score_elimination = ArcheryScoring::select(
            "archery_event_participant_members.*",
            "archery_scorings.id as scoring_id",
        )
            ->join("archery_event_participant_members", "archery_event_participant_members.id", "=", "archery_scorings.participant_member_id")
            ->join("archery_event_participants", "archery_event_participants.id", "=", "archery_event_participant_members.archery_event_participant_id")
            ->where('archery_event_participants.status', 1)
            ->where('archery_event_participants.event_category_id', $category_id)
            ->where("archery_scorings.type", 2)
            ->distinct()
            ->get();


        if ($participant_collection_score_elimination->count() > 0) {
            throw new BLoCException("sudah ada yang melakukan eliminasi");
        }

        if ($participant_collection_have_shoot_off->count() > 0) {
            throw new BLoCException("masih terdapat peserta yang harus melakukan shoot off");
        }

        $template = ArcheryEventEliminationSchedule::makeTemplate($qualification_rank, $elimination_member_count);

        $elimination = ArcheryEventElimination::where("event_category_id", $category_id)->first();
        if ($elimination) {
            throw new BLoCException("elimination sudah ditentukan");
        }
        $elimination = new ArcheryEventElimination;
        $elimination->event_category_id = $category_id;
        $elimination->count_participant = $elimination_member_count;
        $elimination->elimination_type = $match_type;
        $elimination->elimination_scoring_type = $type_scoring;
        $elimination->gender = "none";
        $elimination->save();

        foreach ($template as $key => $value) {
            foreach ($value["seeds"] as $k => $v) {
                foreach ($v["teams"] as $i => $team) {
                    $elimination_member_id = 0;
                    $member_id = isset($team["id"]) ? $team["id"] : 0;
                    $thread = $k;
                    $position_qualification = isset($team["postition"]) ? $team["postition"] : 0;
                    if ($member_id != 0) {
                        $em = ArcheryEventEliminationMember::where("member_id", $member_id)->first();
                        if ($em) {
                            $elimination_member = $em;
                        } else {
                            $elimination_member = new ArcheryEventEliminationMember;
                            $elimination_member->thread = $thread;
                            $elimination_member->member_id = $member_id;
                            $elimination_member->position_qualification = $position_qualification;
                            $elimination_member->save();
                        }
                        $elimination_member_id = $elimination_member->id;
                    }

                    $match = new ArcheryEventEliminationMatch;
                    $match->event_elimination_id = $elimination->id;
                    $match->elimination_member_id = $elimination_member_id;
                    $match->elimination_schedule_id = 0;
                    $match->round = $key + 1;
                    $match->match = $k + 1;
                    $match->index = $i;
                    if (isset($team["win"]))
                        $match->win = $team["win"];

                    $match->gender = "none";
                    $match->save();
                }
            }
        }
        ArcherySeriesUserPoint::setMemberQualificationPoint($category_id);

        return $template;
    }

    private function makeTemplateTeam($group, $category_team, $elimination_member_count, $scoring_type)
    {
        if ($group == "mix_team") {
            $lis_team = ArcheryScoring::mixTeamBestOfThree($category_team);
            $template = ArcheryEventEliminationSchedule::makeTemplateTeam($lis_team, $elimination_member_count);
        } else {
            $team_cat = ($group) == "male_team" ? "individu male" : "individu female";
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
            $template = ArcheryEventEliminationSchedule::makeTemplateTeam($lis_team, $elimination_member_count);
        }

        $elimination_group = ArcheryEventEliminationGroup::where("category_id", $category_team->id)->first();
        if ($elimination_group) {
            throw new BLoCException("elimination sudah ditentukan");
        }
        $elimination_group = new ArcheryEventEliminationGroup;
        $elimination_group->category_id = $category_team->id;
        $elimination_group->count_participant = $elimination_member_count;
        $elimination_group->elimination_scoring_type = $scoring_type;
        $elimination_group->save();

        if (count($lis_team) > 0) {
            foreach ($lis_team as $value1) {
                if (count($value1["teams"]) > 0) {
                    foreach ($value1["teams"] as $value2) {
                        ArcheryEventEliminationGroupMemberTeam::create([
                            "participant_id" => $value1["participant_id"],
                            "member_id" => $value2["id"]
                        ]);
                    }
                }
            }
        }

        foreach ($template as $key => $value) {
            foreach ($value["seeds"] as $k => $v) {
                foreach ($v["teams"] as $i => $team) {
                    $elimination_group_team_id = 0;
                    $participant_id = isset($team["participant_id"]) ? $team["participant_id"] : 0;
                    $thread = $k;
                    $position = isset($team["postition"]) ? $team["postition"] : 0;
                    if ($participant_id != 0) {
                        $team_name = "";
                        foreach ($lis_team as $lt) {
                            if ($lt["participant_id"] == $participant_id) {
                                $team_name = $lt["team"];
                            }
                        }
                        $em = ArcheryEventEliminationGroupTeams::where("participant_id", $participant_id)->first();
                        if ($em) {
                            $elimination_team = $em;
                        } else {
                            $elimination_team = new ArcheryEventEliminationGroupTeams;
                            $elimination_team->thread = $thread;
                            $elimination_team->participant_id = $participant_id;
                            $elimination_team->position = $position;
                            $elimination_team->team_name = $team_name;
                            $elimination_team->save();
                        }
                        $elimination_group_team_id = $elimination_team->id;
                    }

                    $match = new ArcheryEventEliminationGroupMatch;
                    $match->elimination_group_id = $elimination_group->id;
                    $match->group_team_id = $elimination_group_team_id;
                    $match->round = $key + 1;
                    $match->match = $k + 1;
                    $match->index = $i;
                    if (isset($team["win"])) {
                        $match->win = $team["win"];
                    }
                    $match->save();
                }
            }
        }
        return $template;
    }
}
