<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ArcheryClub;
use App\Models\ArcheryEventParticipant;
use DAI\Utils\Exceptions\BLoCException;

class ArcheryEventParticipantMember extends Model
{
    protected $guarded = ['id'];

    public static function resetHaveShootOffMember(ArcheryEventCategoryDetail $category)
    {
        $archery_event_participant_members = ArcheryEventParticipantMember::join("archery_event_participants", "archery_event_participants.id", "=", "archery_event_participant_members.archery_event_participant_id")
            ->where("archery_event_participants.event_category_id", $category->id)
            ->where("status", 1);

        foreach ($archery_event_participant_members as $member) {
            $member->have_shoot_off = 0;
            $member->save();
        }

        return $archery_event_participant_members;
    }

    public static function updateHaveShootOffMember(ArcheryEventCategoryDetail $category)
    {
        $archery_event_score = ArcheryScoring::getScoringRank($category->distance_id, $category->team_category_id, $category->competition_category_id, $category->age_category_id, $category->gender_category, 1, $category->event_id);

        $max_arrow = ($category->count_stage * $category->count_shot_in_stage) * $category->session_in_qualification;

        $participant_is_present = ArcheryEventParticipantMember::select(
            "archery_event_participant_members.id",
            "archery_event_participant_members.name",
            "archery_event_participant_members.have_shoot_off",
            "archery_event_participants.is_present"
        )->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->where('archery_event_participants.status', 1)
            ->where('archery_event_participants.event_category_id', $category->id)
            ->where("archery_event_participants.is_present", 1)
            ->get();

        $elimination_template = $category->default_elimination_count;
        // cek apakah peserta yang is_preasent 1 lebih besar dari elimination template
        if ($elimination_template > 0 && $participant_is_present->count() > $elimination_template) {
            // cek apakah archer terakhir sesuai di yang sesuai template eliminasi udah melakukan shoot secara lengkap
            if ($archery_event_score[$elimination_template - 1]["total_arrow"] == $max_arrow) {
                // cek apakah terdapat total point yang sama anatar peringkat terakhir dan peringkat setelah terakhir
                if ($archery_event_score[$elimination_template - 1]["total"] === $archery_event_score[$elimination_template]["total"]) {
                    $total = $archery_event_score[$elimination_template - 1]["total"];
                    foreach ($archery_event_score as $key => $value) {
                        $member = ArcheryEventParticipantMember::find($value["member"]->id);
                        if ($value["member"]->is_present == 1) {
                            if ($value["total"] === $total) {
                                $scooring_session_11_member = ArcheryScoring::where("scoring_session", 11)
                                    ->where("participant_member_id", $member->id)
                                    ->first();

                                if (!$scooring_session_11_member || $scooring_session_11_member->total == 0) {
                                    $member->have_shoot_off = 1;
                                } else {
                                    $member->have_shoot_off = 2;
                                }

                                $member->have_coint_tost = 0;
                                $member->rank_can_change = null;
                                $member->save();
                            }
                        }
                    }
                }
            }
        }

        return $archery_event_score;
    }

    public static function resetHaveCoinTostMember(ArcheryEventCategoryDetail $category)
    {
        $archery_event_participant_members = ArcheryEventParticipantMember::select("archery_event_participant_members.*")->join("archery_event_participants", "archery_event_participants.id", "=", "archery_event_participant_members.archery_event_participant_id")
            ->where("archery_event_participants.event_category_id", $category->id)
            ->where("archery_event_participants.status", 1)
            ->get();

        foreach ($archery_event_participant_members as $key => $member) {
            $member->have_coint_tost = 0;
            $member->rank_can_change = null;
            $member->save();
        }

        return $archery_event_participant_members;
    }

    public static function updateHaveCoinTostMember(ArcheryEventCategoryDetail $category)
    {
        $elimination_template = $category->default_elimination_count;
        if ($elimination_template == 0) {
            throw new BLoCException("elimination template have't set");
        }

        $archery_event_score = ArcheryScoring::getScoringRankByCategoryId($category->id, 1, $category->getArraySessionCategory(), false, null, false);
        foreach ($archery_event_score as $i => $v) {
            $member_i = ArcheryEventParticipantMember::find($v["member"]["id"]);
            if (!$member_i) {
                throw new BLoCException("member_i not found");
            }

            if ($v["member"]["is_present"] != 1) {
                continue;
            }

            $member_rank_i = MemberRank::where("member_id", $member_i->id)->first();
            if ($member_rank_i->rank > $elimination_template) {
                continue;
            }

            foreach ($archery_event_score as $j => $v2) {
                if (
                    $v["total"] == $v2["total"]
                    && $v["total_x"] == $v2["total_x"]
                    && $v["total_x_plus_ten"] == $v2["total_x_plus_ten"]
                    && $v["member"]["id"] !=  $v2["member"]["id"]
                ) {
                    $member_j = ArcheryEventParticipantMember::find($v2["member"]["id"]);
                    if (!$member_j) {
                        throw new BLoCException("member_j not found");
                    }
                    $member_rank_j = MemberRank::where("member_id", $member_j->id)->first();

                    $rank_can_change = [];
                    if ($member_i->rank_can_change != null) {
                        $rank_can_change = json_decode($member_i->rank_can_change);
                        if (!in_array($member_rank_j->rank, $rank_can_change)) {
                            $rank_can_change[] = $member_rank_j->rank;
                        }
                    } else {
                        $rank_can_change[] = $member_rank_j->rank;
                    }

                    $member_i->have_coint_tost = 1;
                    $member_i->rank_can_change = json_encode($rank_can_change);
                    $member_i->save();
                }
            }
        }
    }

    protected function memberDetail($participant_member_id)
    {
        $member = $this->find($participant_member_id);
        $participant = ArcheryEventParticipant::find($member->archery_event_participant_id);
        $archery_event = ArcheryEvent::find($participant->event_id);
        $flat_categorie = $archery_event->flatCategories;
        $category_label = $participant->team_category_id . "-" . $participant->age_category_id . "-" . $participant->competition_category_id . "-" . $participant->distance_id . "m";
        foreach ($flat_categorie as $key => $value) {
            if (
                $value->age_category_id == $participant->age_category_id
                && $value->competition_category_id == $participant->competition_category_id
                && $value->team_category_id == $participant->team_category_id
                && $value->distance_id == $participant->distance_id
            ) {
                $category_label = $value->archery_event_category_label;
            }
        }

        $participant->category_label = $category_label;
        $participant->member = $member;
        $club = ArcheryClub::find($participant->club_id);
        $participant->club = $club ? $club->name : "";
        return $participant;
    }
}
