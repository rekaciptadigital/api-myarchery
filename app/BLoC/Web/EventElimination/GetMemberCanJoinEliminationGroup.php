<?php

namespace App\BLoC\Web\EventElimination;

use DAI\Utils\Abstracts\Transactional;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventEliminationGroupMemberTeam;
use DAI\Utils\Exceptions\BLoCException;
use App\Models\ArcheryEventParticipant;

class GetMemberCanJoinEliminationGroup extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $category_detail_group_id = $parameters->get("category_detail_group_id");
        $participant_id = $parameters->get("participant_id");
        // dapatkan participant dari param
        $participant_group = ArcheryEventParticipant::find($participant_id);
        if (!$participant_group) {
            throw new BLoCException("participant not found");
        }
        $category_detail_group = ArcheryEventCategoryDetail::find($category_detail_group_id);
        if (!$category_detail_group) {
            throw new BLoCException("category not found");
        }

        return ArcheryEventEliminationGroup::getMemberCanJoinEliminationGroup($category_detail_group_id, $participant_id);
    }

    protected function validation($parameters)
    {
        return [
            "category_detail_group_id" => "required",
            "participant_id" => "required"
        ];
    }
}
