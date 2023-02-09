<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use App\Models\MemberRank;

class ChangeRankMemberQualification extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $member_id = $parameters->get("member_id");
        $rank = $parameters->get("rank");

        $member = ArcheryEventParticipantMember::find($member_id);
        if ($member->have_coint_tost != 1) {
            throw new BLoCException("member not have coint tost");
        }

        $participant = ArcheryEventParticipant::find($member->archery_event_participant_id);
        if (!$participant) {
            throw new BLoCException("participant not found");
        }

        $event_elimination = ArcheryEventElimination::where("event_category_id", $participant->event_category_id)->first();
        if ($event_elimination) {
            throw new BLoCException("eliminasi sudah ditentukan");
        }

        if ($member->rank_can_change == null) {
            throw new BLoCException("rank can change null");
        }

        if (!in_array($rank, json_decode($member->rank_can_change))) {
            throw new BLoCException("invalid rank input");
        }

        $member_rank = MemberRank::where("member_id", $member->id)->first();
        if (!$member_rank) {
            throw new BLoCException("member rank not found");
        }

        $list_member_rank = MemberRank::where("category_id", $participant->event_category_id)->get();
        foreach ($list_member_rank as $mr) {
            if ($mr->id == $member_rank->id) {
                continue;
            }

            if ($mr->rank == $rank) {
                $mr->rank = $member_rank->rank;
            }
        }

        $member_rank->rank = $rank;
        $member_rank->save();

        return [
            "member_rank" => $member_rank,
            "member" => $member
        ];
    }

    protected function validation($parameters)
    {
        return [
            "member_id" => "required|exists:archery_event_participant_members,id",
            "rank" => "required|integer"
        ];
    }
}
