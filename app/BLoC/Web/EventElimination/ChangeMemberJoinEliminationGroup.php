<?php

namespace App\BLoC\Web\EventElimination;

use DAI\Utils\Abstracts\Transactional;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventEliminationGroupMemberTeam;
use App\Models\ArcheryEventEliminationGroupTeams;
use DAI\Utils\Exceptions\BLoCException;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;

class ChangeMemberJoinEliminationGroup extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $member_id_old = $parameters->get("member_id_old");
        $member_old = ArcheryEventParticipantMember::find($member_id_old);
        if (!$member_old) {
            throw new BLoCException("member old not found");
        }
        $member_id_new = $parameters->get("member_id_new");
        $participant_id = $parameters->get("participant_id");
        $category_group_id = $parameters->get("category_id");

        $category_group = ArcheryEventCategoryDetail::find($category_group_id);
        if (!$category_group) {
            throw new BLoCException("category not found");
        }

        $participant_group = ArcheryEventParticipant::find($participant_id);
        if (!$participant_group) {
            throw new BLoCException("participant not found");
        }

        $elimination_group_member_team = ArcheryEventEliminationGroupMemberTeam::where("participant_id", $participant_id)->where("member_id", $member_id_old)->first();
        if (!$elimination_group_member_team) {
            throw new BLoCException("eelimination_group_member_team not found");
        }

        $elimination_group_team = ArcheryEventEliminationGroupTeams::where("participant_id", $participant_id)->first();
        if (!$elimination_group_team) {
            throw new BLoCException("this participant not join elimination");
        }

        $member_can_join = ArcheryEventEliminationGroup::getMemberCanJoinEliminationGroup($category_group_id, $participant_id);
        $can_join = false;
        foreach ($member_can_join as $m) {
            if ($m->member_id == $member_id_new) {
                $can_join = true;
            }
        }

        if ($can_join == true) {
            $elimination_group_member_team->member_id = $member_id_new;
            $elimination_group_member_team->save();
            return "success";
        } else {
            throw new BLoCException("can change member");
        }
    }

    protected function validation($parameters)
    {
        return [
            "member_id_old" => "required",
            "member_id_new" => "required",
            "participant_id" => "required",
            "category_id" => "required"
        ];
    }
}
