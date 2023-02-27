<?php

namespace App\BLoC\App\ArcheryEvent;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\TeamMemberSpecial;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;

class EntryByNameParticipantTeam extends Transactional
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

        $participant_team = ArcheryEventParticipant::select("archery_event_participants.*", "archery_events.with_contingent")
            ->join("archery_events", "archery_events.id", "=", "archery_event_participants.event_id")
            ->where("archery_event_participants.id", $participant_team_id)
            ->where("archery_event_participants.status", 1)
            ->where("archery_event_participants.type", "team")
            ->first();

        if (!$participant_team) {
            throw new BLoCException("participant team not found");
        }

        $with_contingent = $participant_team->with_contingent;

        $category_team = ArcheryEventCategoryDetail::find($participant_team->event_category_id);

        if ($is_entry_by_name == 0) {
            if ($participant_team->is_special_team_member == 1) {
                TeamMemberSpecial::deleteMemberSpecial($participant_team, $with_contingent);
            }
        } else {
            TeamMemberSpecial::deleteMemberSpecial($participant_team, $with_contingent);
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

                foreach ($member_list as $ml) {
                    $participant_individu_id = $ml["participant_id"];
                    $participant_individu = ArcheryEventParticipant::where("id", $participant_individu_id)
                        ->where("status", 1)
                        ->where("event_category_id", $category_individu->id);

                    if ($with_contingent == 0) {
                        $participant_individu->where("club_id", $participant_team->club_id);
                    } else {
                        $participant_individu->where("city_id", $participant_team->city_id);
                    }

                    $participant_individu =  $participant_individu->first();

                    if (!$participant_individu) {
                        throw new BLoCException("participant individu not found");
                    }

                    $team_member_special = TeamMemberSpecial::join("archery_event_participants", "archery_event_participants.id", "=", "team_member_special.participant_team_id")
                        ->where("team_member_special.participant_individual_id", $participant_individu_id)
                        // ->where("team_member_special.participant_team_id", $participant_team_id)
                        ->where("archery_event_participants.event_category_id", $participant_team->event_category_id)
                        ->first();

                    if ($team_member_special) {
                        throw new BLoCException("peserta ini sudah di pilih sebagai member team di tim lain");
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
                    ->where("event_category_id", $category_individu_male->id);

                if ($with_contingent == 0) {
                    $participant_individu_male->where("club_id", $participant_team->club_id);
                } else {
                    $participant_individu_male->where("city_id", $participant_team->city_id);
                }
                $participant_individu_male = $participant_individu_male->get();

                $participant_individu_female = ArcheryEventParticipant::where(function ($query) use ($participant_individu_id_0, $participant_individu_id_1) {
                    $query->where("id", $participant_individu_id_0)
                        ->orWhere("id", $participant_individu_id_1);
                })->where("status", 1)
                    ->where("event_category_id", $category_individu_female->id);

                if ($with_contingent == 0) {
                    $participant_individu_female->where("club_id", $participant_team->club_id);
                } else {
                    $participant_individu_female->where("city_id", $participant_team->city_id);
                }
                $participant_individu_female = $participant_individu_female->get();

                if ($participant_individu_male->count() != 1) {
                    throw new BLoCException("invalid participant male count");
                }

                if ($participant_individu_female->count() != 1) {
                    throw new BLoCException("invalid participant female count");
                }

                foreach ($member_list as $ml) {
                    $team_member_special = TeamMemberSpecial::join("archery_event_participants", "archery_event_participants.id", "=", "team_member_special.participant_team_id")
                        ->where("team_member_special.participant_individual_id", $ml["participant_id"])
                        // ->where("team_member_special.participant_team_id", $participant_team_id)
                        ->where("archery_event_participants.event_category_id", $participant_team->event_category_id)
                        ->first();

                    if ($team_member_special) {
                        throw new BLoCException("peserta ini sudah di pilih sebagai member team di tim lai");
                    }

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
