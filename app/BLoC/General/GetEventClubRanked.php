<?php

namespace App\BLoC\General;

use DAI\Utils\Abstracts\Retrieval;
use App\Libraries\ClubRanked;
use App\Models\ArcheryClub;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventEliminationMember;
use App\Models\ArcheryEventParticipant;

class GetEventClubRanked extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get("event_id");

        // dapat list club yang joint event baik individu maupun beregu
        $clubs = ArcheryClub::all();
        $list_club_join_event = [];
        foreach ($clubs as $key => $club) {
            $participant = ArcheryEventParticipant::where("event_id", $event_id)->where("status", 1)->where("club_id", $club->id)->first();
            if ($participant) {
                $list_club_join_event[] = $club;
            }
        }

        $category_events = ArcheryEventCategoryDetail::where("event_id", $event_id)
            ->where("is_show", 1)
            ->get()
            ->groupBy(["competition_category_id", "age_category_id"]);

        $list_club_with_medal = [];
        foreach ($list_club_join_event as $key => $club) {
            $detail_club_with_medal = [];
            $detail_club_with_medal["club_name"] = $club->name;
            $total_gold_medal = 0;
            $total_silver_medal = 0;
            $total_bronze_medal = 0;
            $list_medal = [];
            foreach ($category_events as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    $gold_medal = 0;
                    $silver_medal = 0;
                    $bronze_medal = 0;
                    foreach ($value2 as $key3 => $value3) {
                        if ($value3->categoryTeam == "Individual") {
                            $participant_join_category_by_club =  ArcheryEventParticipant::select("archery_event_participants.id as participant_id", "archery_event_participant_members.id as member_id", "archery_event_participants.club_id", "archery_event_participants.user_id")
                                ->join("archery_event_participant_members", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
                                ->where("event_category_id", $value3->id)
                                ->where("status", 1)
                                ->where("is_present", 1)
                                ->where("club_id", $club->id)
                                ->get();

                            if ($participant_join_category_by_club->count() > 0) {
                                foreach ($participant_join_category_by_club as $member) {
                                    $elimination_member = ArcheryEventEliminationMember::where("member_id", $member->member_id)->first();
                                    if ($elimination_member->position_qualification == 1) {
                                        $gold_medal = $gold_medal + 1;
                                        $total_gold_medal = $total_gold_medal + 1;
                                    }

                                    if ($elimination_member->position_qualification == 2) {
                                        $silver_medal = $silver_medal + 1;
                                        $total_silver_medal = $total_silver_medal + 1;
                                    }

                                    if ($elimination_member->position_qualification == 3) {
                                        $bronze_medal = $bronze_medal + 1;
                                        $total_bronze_medal = $total_bronze_medal + 1;
                                    }

                                    if ($elimination_member->elimination_ranked == 3) {
                                        $bronze_medal = $bronze_medal + 1;
                                    }

                                    if ($elimination_member->elimination_ranked == 2) {
                                        $silver_medal = $silver_medal + 1;
                                    }

                                    if ($elimination_member->elimination_ranked == 1) {
                                        $gold_medal = $gold_medal + 1;
                                    }
                                }
                            }
                        } else {
                        }
                    }
                }
            }
        }

        return ClubRanked::getEventRanked($event_id);
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required'
        ];
    }
}
