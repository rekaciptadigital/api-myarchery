<?php

namespace App\BLoC\App\ArcheryEvent;

use App\Models\ArcheryClub;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\TeamMemberSpecial;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class GetParticipantMemberByCategory extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $participant = ArcheryEventParticipant::find($parameters->get('participant_id'));

        $club = ArcheryClub::find($participant->club_id);

        $output = [];

        $user_member = [];
        if ($participant->type == "individual") {
            $user_member = User::find($participant->user_id);
            if (!$user_member) {
                throw new BLoCException("user tidak ditemukan");
            }
            $archery_member = ArcheryEventParticipantMember::where("archery_event_participant_id", $participant->id)->first();
            if (!$archery_member) {
                throw new BLoCException("data member tidak ditemukan");
            }

            $user_member['member_id'] = $archery_member->id;
        } else {
            $gender_category = $participant->team_category_id;
            $category_team = ArcheryEventParticipant::select("archery_event_participants.*")
                ->where("archery_event_participants.age_category_id", $participant->age_category_id)
                ->where("archery_event_participants.club_id", $participant->club_id)
                ->where("archery_event_participants.status", 1)
                ->where("archery_event_participants.event_id", $participant->event_id)
                ->where("archery_event_participants.competition_category_id", $participant->competition_category_id)
                ->where("archery_event_participants.distance_id", $participant->distance_id)
                ->where(function ($query) use ($gender_category) {
                    if ($gender_category == "male_team") {
                        $query->where("archery_event_participants.team_category_id", "individu male");
                    }
                    if ($gender_category == "female_team") {
                        $query->where("archery_event_participants.team_category_id", "individu female");
                    }
                    if ($gender_category == "mix_team") {
                        $query->whereIn("archery_event_participants.team_category_id", ["individu male", "individu female"]);
                    }
                })
                ->get();

            if ($category_team->count() > 0) {
                foreach ($category_team as $ct) {
                    $user = User::find($ct->user_id);
                    if (!$user) {
                        throw new BLoCException("user tidak ada");
                    }

                    $user->participant_id = $ct->id;

                    $check_member_selected_team = TeamMemberSpecial::where("participant_individual_id", $ct->id)
                        ->where("participant_team_id", $participant->id)
                        ->first();
                    $is_selected_for_team = 0;
                    if ($check_member_selected_team) {
                        $is_selected_for_team = 1;
                    }

                    $user->is_selected_for_team = $is_selected_for_team;
                    array_push($user_member, $user);
                }
            }
        }

        $participant['members'] = $user_member;

        $event_category = ArcheryEventCategoryDetail::find($participant->event_category_id);

        $detail_participant_user = User::find($participant->user_id);
        if (!$detail_participant_user) {
            throw new BLoCException("user participant tidak ditemukan");
        }

        $output['participant'] = [
            "participant_id" => $participant->id,
            "event_id" => $participant->event_id,
            "user_id" => $participant->user_id,
            "name" => $detail_participant_user->name,
            "type" => $participant->type,
            "email" => $detail_participant_user->email,
            "phone_number" => $detail_participant_user->phone_number,
            "age" => $detail_participant_user->age,
            "gender" => $detail_participant_user->gender,
            "transaction_log_id" => $participant->transaction_log_id,
            "team_name" => $participant->team_name,
        ];
        $output['event_category_detail'] = $event_category ? $event_category->getCategoryDetailById($event_category->id) : null;
        $output['member'] = $user_member;
        $output['club'] = $club != null ? $club : [];

        return $output;
    }

    protected function validation($parameters)
    {
        return [
            'participant_id' => 'required|integer|exists:archery_event_participants,id'
        ];
    }
}
