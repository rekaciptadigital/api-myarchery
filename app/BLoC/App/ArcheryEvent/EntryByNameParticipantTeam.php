<?php

namespace App\BLoC\App\ArcheryEvent;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\TeamMemberSpecial;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class EntryByNameParticipantTeam extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $participant_team_id = $parameters->get("participant_team_id");
        $is_entry_by_name = $parameters->get("is_entry_by_name");
        $member_list = $parameters->get("members");

        $participant_team = ArcheryEventParticipant::where("id", $participant_team_id)
            ->where("status", 1)
            ->where("club_id", ">", 0)
            ->where("type", "team")
            ->first();

        if (!$participant_team) {
            throw new BLoCException("participant team not found");
        }

        $category_team = ArcheryEventCategoryDetail::find($participant_team->event_category_id);

        if ($is_entry_by_name == 0) {
            $team_member_special_list = TeamMemberSpecial::where("participant_team_id", $participant_team_id)
                ->get();
            foreach ($team_member_special_list as $tmsl_key => $tmsl) {
                $tmsl->delete();
            }

            $participant_team->is_special_team_member = 0;
            $participant_team->save();
        } else {
            if ($participant_team->team_category_id != "mix_team") {
                if (count($member_list) != 3) {
                    throw new BLoCException("member harus terdiri dari 3 anggota");
                }
                $team_category_id = $category_team->team_category_id == "male_team" ? "individu male" : "individu female";

                $category_individu = ArcheryEventCategoryDetail::where("event_id", $participant_team->event_id)
                    ->where("age_category_id", $participant_team->age_category_id)
                    ->where("distance_id", $participant_team->distance_id)
                    ->where("competition_category_id", $participant_team->competition_category_id)
                    ->where("team_category_id", $team_category_id)
                    ->first();

                if (!$category_individu) {
                    throw new BLoCException("category individu not found");
                }

                foreach ($member_list as $ml_key => $ml) {
                    $participant_individu_id = $ml["participant_id"];
                    $participant_individu = ArcheryEventParticipant::where("id", $participant_individu_id)
                        ->where("status", 1)
                        ->where("event_category_id", $category_individu->id)
                        ->where("club_id", $participant_team->club_id)
                        ->first();

                    if (!$participant_individu) {
                        throw new BLoCException("participant individu not found");
                    }

                    $team_member_special = new TeamMemberSpecial();
                    $team_member_special->participant_individual_id = $participant_individu_id;
                    $team_member_special->participant_team_id = $participant_team_id;
                    $team_member_special->save();
                }
            }

            if ($participant_team->team_category_id == "mix_team") {
                if (count($member_list) != 2) {
                    throw new BLoCException("member harus terdiri dari 2 anggota");
                }

                $category_individu_male = ArcheryEventCategoryDetail::where("event_id", $participant_team->event_id)
                    ->where("age_category_id", $participant_team->age_category_id)
                    ->where("distance_id", $participant_team->distance_id)
                    ->where("competition_category_id", $participant_team->competition_category_id)
                    ->where("team_category_id", "individu male")
                    ->first();

                if (!$category_individu_male) {
                    throw new BLoCException("category individu male not found");
                }

                $category_individu_female = ArcheryEventCategoryDetail::where("event_id", $participant_team->event_id)
                    ->where("age_category_id", $participant_team->age_category_id)
                    ->where("distance_id", $participant_team->distance_id)
                    ->where("competition_category_id", $participant_team->competition_category_id)
                    ->where("team_category_id", "individu female")
                    ->first();

                if (!$category_individu_female) {
                    throw new BLoCException("category individu female not found");
                }

                $participant_individu_id_0 = $member_list[0];
                $participant_individu_id_1 = $member_list[1];

                $participant_individu_male = ArcheryEventParticipant::where(function ($query) use ($participant_individu_id_0, $participant_individu_id_1) {
                    $query->where("id", $participant_individu_id_0)
                        ->orWhere("id", $participant_individu_id_1);
                })->where("status", 1)
                    ->where("event_category_id", $category_individu_male->id)
                    ->where("club_id", $participant_team->club_id)
                    ->get();

                $participant_individu_female = ArcheryEventParticipant::where(function ($query) use ($participant_individu_id_0, $participant_individu_id_1) {
                    $query->where("id", $participant_individu_id_0)
                        ->orWhere("id", $participant_individu_id_1);
                })->where("status", 1)
                    ->where("event_category_id", $category_individu_female->id)
                    ->where("club_id", $participant_team->club_id)
                    ->get();

                if ($participant_individu_male->count() != 1) {
                    throw new BLoCException("invalid participant male count");
                }

                if ($participant_individu_female->count() != 1) {
                    throw new BLoCException("invalid participant female count");
                }

                foreach ($member_list as $ml_key => $ml) {
                    $team_member_special = new TeamMemberSpecial();
                    $team_member_special->participant_individual_id = $ml["participant_id"];
                    $team_member_special->participant_team_id = $participant_team_id;
                    $team_member_special->save();
                }
            }

            $participant_team->is_special_team_member = 1;
            $participant_team->save();
        }

        return TeamMemberSpecial::where("participant_team_id", $participant_team_id)->get();
    }

    protected function validation($parameters)
    {
        $rules = [];
        $rules["is_entry_by_name"] = "required|in:0,1";
        $rules["participant_team_id"] = "required|exists:archery_event_participants,id";

        if ($parameters->get("is_entry_by_name") == 1) {
            $rules["members"] =  "required|array";
            $rules["members.*.participant_id"] =  "required|exists:archery_event_participants,id";
        }
        return $rules;
    }
}
