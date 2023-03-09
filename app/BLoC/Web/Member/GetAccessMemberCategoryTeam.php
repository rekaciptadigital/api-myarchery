<?php

namespace App\BLoC\Web\Member;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class GetAccessMemberCategoryTeam extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $participant_id = $parameters->get("participant_id");

        // cari participant berdsarkan id dan type nya == Team
        $participant = ArcheryEventParticipant::select("archery_event_participants.*")
            ->join("archery_event_category_details", "archery_event_category_details.id", "=", "archery_event_participants.event_category_id")
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
            ->where("archery_event_participants.id", $participant_id)
            ->where("archery_master_team_categories.type", "Team")
            ->first();
        if (!$participant) {
            throw new BLoCException("participant not found");
        }

        // query category berdasarkan event id dan array team category id
        $categories = ArcheryEventCategoryDetail::select("archery_event_category_details.*", "archery_master_age_categories.max_age", "archery_master_age_categories.min_age")
            ->join("archery_master_age_categories", "archery_master_age_categories.id", "archery_event_category_details.age_category_id")
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
            ->where("archery_event_category_details.event_id", $participant->event_id)
            ->whereIn("archery_master_team_categories.id", ["male_team", "female_team", "mix_team"])
            ->get();

        // cek jika array category kosong
        if ($categories->count() == 0) {
            throw new BLoCException("categories were not found");
        }

        // tambahkan quota left di response category
        foreach ($categories as $category) {
            $countUserBooking = ArcheryEventParticipant::countEventUserBooking($category->id);
            $category->countUserBooking = $countUserBooking;
        }

        return $categories;
    }

    protected function validation($parameters)
    {
        return [
            'participant_id' => 'required|exists:archery_event_participants,id',
        ];
    }
}
