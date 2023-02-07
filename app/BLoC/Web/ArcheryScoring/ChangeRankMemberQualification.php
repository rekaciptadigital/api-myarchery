<?php

namespace App\BLoC\Web\ArcheryScoring;

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

        if (!in_array($rank, $member->rank_can_join)) {
            throw new BLoCException("invalid rank input");
        }

        $member->have_coint_tost = 2;
        $member->save();

        $member_rank = MemberRank::where("member_id", $member->id)->first();
        if (!$member_rank) {
            throw new BLoCException("member rank not found");
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
