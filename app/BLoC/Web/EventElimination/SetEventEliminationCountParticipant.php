<?php

namespace App\BLoC\Web\EventElimination;

use DAI\Utils\Abstracts\Transactional;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Exceptions\BLoCException;

class SetEventEliminationCountParticipant extends Transactional
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

        $event_elimination = ArcheryEventElimination::where("event_category_id", $category->id)->first();
        if ($event_elimination) {
            throw new BLoCException("tidak bisa mengubah peserta eliminasi karena eliminasi telah ditentukan");
        }

        $participants_collection = ArcheryEventParticipantMember::select(
            "archery_event_participant_members.id",
            "archery_event_participant_members.user_id",
            "archery_event_participants.id as participant_id",
            "archery_event_participants.event_id",
            "archery_event_participants.is_present",
            "archery_scorings.scoring_session",
            "archery_event_participant_members.have_shoot_off"
        )
            ->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
            ->join("archery_scorings", "archery_scorings.participant_member_id", "=", "archery_event_participant_members.id")
            ->where('archery_event_participants.status', 1)
            ->where('archery_event_participants.event_category_id', $category->id)
            ->where(function ($query) {
                return $query->where("archery_scorings.type", 2)->orWhere("archery_scorings.scoring_session", 11);
            })->get();

        if ($participants_collection->count() > 0) {
            throw new BLoCException("tidak dapat mengubah jumlah peserta karena telah terdapat yang melakukan eliminasi atau melakukan shoot off");
        }

        $category->update([
            "default_elimination_count" => $parameters->get("count_elimination_participant")
        ]);

        return "success";
    }

    protected function validation($parameters)
    {
        return [
            'event_category_id' => 'required|exists:archery_event_category_details,id',
            "count_elimination_participant" => "required|in:0,8,16,32,64,128"
        ];
    }
}
