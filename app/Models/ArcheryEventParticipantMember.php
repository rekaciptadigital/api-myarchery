<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ArcheryClub;
use App\Models\ArcheryEventParticipant;
use DAI\Utils\Exceptions\BLoCException;

class ArcheryEventParticipantMember extends Model
{
    protected $guarded = ['id'];


    public static function saveArcheryEventParticipantMember(ArcheryEventParticipant $participant, User $user, ArcheryEventCategoryDetail $category, $is_series = 0)
    {
        $member = new ArcheryEventParticipantMember();
        $member->archery_event_participant_id = $participant->id;
        $member->name = $user->name;
        $member->team_category_id = $category->team_category_id;
        $member->email = $user->email;
        $member->phone_number = $user->phone_number;
        $member->club = null;
        $member->age = $user->age;
        $member->gender = $user->gender;
        $member->qualification_date = null;
        $member->birthdate = $user->date_of_birth;
        $member->user_id = $user->id;
        $member->is_series = $is_series;
        $member->have_shoot_off = 0;
        $member->city_id = 0;
        $member->save();

        return $member;
    }
    
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
            $member->rank_can_change = null;
            $member->save();
        }
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
