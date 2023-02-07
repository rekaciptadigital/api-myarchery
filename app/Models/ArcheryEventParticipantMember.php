<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ArcheryClub;
use App\Models\ArcheryEventParticipant;
use DAI\Utils\Exceptions\BLoCException;

class ArcheryEventParticipantMember extends Model
{
    protected $guarded = ['id'];

    public static function resetHaveCoinTostMember(ArcheryEventCategoryDetail $category)
    {
        $archery_event_score = ArcheryScoring::getScoringRankByCategoryId($category->id, 1, $category->getArraySessionCategory(), false, null, false);
        foreach ($archery_event_score as $key => $value) {
            $member_id = $value["member"]["id"];

            $member = ArcheryEventParticipantMember::find($member_id);
            if (!$member) {
                throw new BLoCException("member not found");
            }

            $member->have_coint_tost = 0;
            $member->save();
        }
    }

    public static function updateHaveCoinTostMember(ArcheryEventCategoryDetail $category)
    {
        $elimination_count = $category->default_elimination_count;
        if ($elimination_count == 0) {
            throw new BLoCException("elimination count not found");
        }
        $archery_event_score = ArcheryScoring::getScoringRankByCategoryId($category->id, 1, $category->getArraySessionCategory(), false, null, false);
        for ($i = 0; $i < $elimination_count; $i++) {
            for ($j = 0; $j < $elimination_count; $j++) {
                if (
                    $archery_event_score[$i]["total"] == $archery_event_score[$j]["total"]
                    && $archery_event_score[$i]["total_x"] == $archery_event_score[$j]["total_x"]
                    && $archery_event_score[$i]["total_x_plus_ten"] == $archery_event_score[$j]["total_x_plus_ten"]
                    && $archery_event_score[$i]["member"]["id"] !=  $archery_event_score[$j]["member"]["id"]
                ) {
                    $member_i = ArcheryEventParticipantMember::find($archery_event_score[$i]["member"]["id"]);
                    if (!$member_i) {
                        throw new BLoCException("member_i not found");
                    }

                    $member_j = ArcheryEventParticipantMember::find($archery_event_score[$j]["member"]["id"]);
                    if (!$member_j) {
                        throw new BLoCException("member_j not found");
                    }
                    $member_rank_j = MemberRank::where("member_id", $member_j->id)->first();
                    if (!$member_rank_j) {
                        $member_rank_j = new MemberRank();
                        $member_rank_j->rank = $j + 1;
                        $member_rank_j->category_id = $category->id;
                        $member_rank_j->member_id = $member_rank_j->id;
                        $member_rank_j->save();
                    }

                    $rank_can_change = [];
                    if ($member_i->rank_can_change) {
                        $rank_can_change = json_decode($member_i->rank_can_change);
                        $rank_can_change[] = $member_rank_j->rank;
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
