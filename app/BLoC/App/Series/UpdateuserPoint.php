<?php

namespace App\BLoC\App\Series;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationMember;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcherySeriesUserPoint;
use DAI\Utils\Abstracts\Transactional;

class UpdateuserPoint extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $category_id = $parameters->get("category_id");
        $category_detail = ArcheryEventCategoryDetail::find($category_id);


        $elimination = ArcheryEventElimination::where("event_category_id", $category_detail->id)
            ->first();

        if ($elimination) {
            ArcherySeriesUserPoint::setMemberQualificationPoint($category_detail->id);
            $participants = ArcheryEventParticipant::select(
                "archery_event_participants.*",
                "archery_event_participant_members.id as member_id"
            )->join(
                "archery_event_participant_members",
                "archery_event_participant_members.archery_event_participant_id",
                "=",
                "archery_event_participants.id"
            )->where("archery_event_participants.event_category_id", $category_detail->id)
                ->where("archery_event_participants.status", 1)
                ->get();

            foreach ($participants as $p_key => $p) {
                $elimination_member = ArcheryEventEliminationMember::where("member_id", $p->member_id)
                    ->first();

                if ($elimination_member) {
                    if ($elimination_member->elimination_ranked > 0) {
                        ArcherySeriesUserPoint::setPoint($p->member_id, "elimination", $elimination_member->elimination_ranked);
                    }
                }
            }
        }

        return "success";
    }

    protected function validation($parameters)
    {
        return [
            "category_id" => "required|exists:archery_event_category_details,id"
        ];
    }
}
