<?php

namespace App\BLoC\Web\EventElimination;

use App\Models\ArcheryScoring;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationSchedule;
use DAI\Utils\Abstracts\Transactional;
use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Exceptions\BLoCException;
use App\Models\ArcheryEventEliminationMember;
use App\Models\ArcherySeriesUserPoint;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventMasterCompetitionCategory;
use App\Models\ArcheryEventParticipantMember;

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

        $score_type = 1; // 1 for type qualification


        $competition_category = ArcheryEventMasterCompetitionCategory::find($category->competition_category_id);
        if (!$competition_category) {
            throw new BLoCException("COMPETITION NAN");
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

        $qualification_rank = ArcheryScoring::getScoringRankByCategoryId($event_category_id, $score_type, $session, false, null, true);


        // cek apakah total peserta yang ikut eliminasi > elimination_member_count
        // if (count($qualification_rank) < $elimination_member_count) {
        //     throw new BLoCException("jumlah peserta lebih sedikit dari jumlah eliminasi");
        // }


        // cek apakah terdapat peserta yang belum melakukan shoot qualifikasi
        if (count($qualification_rank) > 0) {
            foreach ($qualification_rank as $key => $value) {
                if ($value["sessions"][count($session)]["total"] === 0) {
                    throw new BLoCException("terdapat peserta yang belum melakukan shoot kualifikasi secara lengkap");
                }
            }
        }

        $template = ArcheryEventEliminationSchedule::makeTemplate($qualification_rank, $elimination_member_count);

        // cek apakah ada yang telah melakukan shoot di eliminasi
        $participants_query = ArcheryEventParticipantMember::select(
            "archery_event_participant_members.id",
            "archery_event_participant_members.user_id",
            "archery_event_participants.id as participant_id",
            "archery_event_participants.event_id",
            "archery_event_participants.is_present",
            "archery_scorings.scoring_session",
            "archery_scorings.type",
            "archery_event_participant_members.have_shoot_off"
        )
            ->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->join("archery_scorings", "archery_scorings.participant_member_id", "=", "archery_event_participant_members.id")
            ->where('archery_event_participants.status', 1)
            ->where('archery_event_participants.event_category_id', $event_category_id);

        $participant_collection_score_elimination = $participants_query->where("archery_scorings.type", 2)->get();


        if ($participant_collection_score_elimination->count() > 0) {
            throw new BLoCException("sudah ada yang melakukan eliminasi");
        }

        $participant_collection_have_shoot_off = $participants_query->where("archery_event_participant_members.have_shoot_off", 1)->get();
        if ($participant_collection_have_shoot_off->count() > 0) {
            throw new BLoCException("masih terdapat peserta yang harus melakukan shoot off");
        }

        $elimination = ArcheryEventElimination::where("event_category_id", $event_category_id)->first();
        if (!$elimination) {
            $elimination = new ArcheryEventElimination;
        }

        $elimination->event_category_id = $event_category_id;
        $elimination->count_participant = $elimination_member_count;
        $elimination->elimination_type = $match_type;
        $elimination->elimination_scoring_type = $scoring_type;
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

                    $elimination_match = ArcheryEventEliminationMatch::where("elimination_member_id", $elimination_member_id)
                        ->where("event_elimination_id", $elimination->id)
                        ->first();
                    if ($elimination_match) {
                        $elimination_match->delete();
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
        ArcherySeriesUserPoint::setMemberQualificationPoint($category->id);
        return $template;
    }

    protected function validation($parameters)
    {
        return [
            'match_type' => 'required',
            'event_category_id' => 'required|exists:archery_event_category_details,id',
        ];
    }
}
