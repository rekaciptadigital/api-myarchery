<?php

namespace App\BLoC\App\ArcheryEvent;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ParticipantMemberTeam;
use App\Models\TemporaryParticipantMember;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class UpdateParticipantMember extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = Auth::guard('app-api')->user();

        $participant = ArcheryEventParticipant::find($parameters->get('participant_id'));
        if (!$participant) {
            throw new BLoCException("category event not found");
        }
        if ($participant->user_id != $user->id) {
            throw new BLoCException("you are not owned this participant");
        }
        $user_ids = $parameters->get('user_id');

        $event_category_detail = ArcheryEventCategoryDetail::find($participant->event_category_id);
        if (!$event_category_detail) {
            throw new BLoCException("event category id not found");
        }

        $gender_category = $event_category_detail->gender_category;

        $participant_member_id = [];

        foreach ($user_ids as $u) {
            $user_register = User::find($u);
            if (!$user_register) {
                throw new BLoCException("user register not found");
            }

            $category = ArcheryEventCategoryDetail::where('event_id', $event_category_detail->event_id)
                ->where('age_category_id', $event_category_detail->age_category_id)
                ->where('competition_category_id', $event_category_detail->competition_category_id)
                ->where('distance_id', $event_category_detail->distance_id)
                ->where('team_category_id', $gender_category == 'mix' ? 'individu ' . $user_register->gender : 'individu ' . $gender_category)
                ->first();

            // cek apakah terdapat category individual
            if (!$category) {
                throw new BLoCException("category individual not found for this category");
            }

            $participant_member_old = ArcheryEventParticipant::join('archery_event_participant_members', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
                ->where('archery_event_participants.event_category_id', $category->id)
                ->where('archery_event_participants.user_id', $u)
                ->get(['archery_event_participant_members.*'])
                ->first();

            if (!$participant_member_old) {
                if ($user->id == $u) {
                    throw new BLoCException("you are not joined individual category for this category");
                }
                throw new BLoCException("user with email " . $user_register->email . " not joined individual category for this category");
            }

            $temporary = TemporaryParticipantMember::join('archery_event_participant_members', 'archery_event_participant_members.id', '=', 'table_temporrary_member.participant_member_id')
                ->join('archery_event_participants', 'archery_event_participants.id', '=', 'table_temporrary_member.participant_id')
                ->join('transaction_logs', 'transaction_logs.id', '=', 'archery_event_participants.transaction_log_id')
                ->where('table_temporrary_member.participant_member_id', $participant_member_old->id)
                ->where('table_temporrary_member.event_category_id', $event_category_detail->id)
                ->get(['transaction_logs.*'])->first();

            if ($temporary) {
                if ($temporary->status == 4 && $temporary->expired_time > time()) {
                    throw new BLoCException("user dengan email " . $user_register->email . " telah didaftarkan pada category ini sebelumnya");
                } elseif ($temporary->status == 2) {
                    throw new BLoCException("order has expired please order again");
                } 
                // elseif ($temporary->status == 1) {
                //     throw new BLoCException("user with email " . $user_register->email . " already join this category");
                // }
            }
            array_push($participant_member_id, $participant_member_old);
            $participant_member_team = ParticipantMemberTeam::where('participant_member_id', $participant_member_old->id)->where('event_category_id', $event_category_detail->id)->first();
            if ($participant_member_team) {
                throw new BLoCException("user with email " . $user_register->email . " already join this category");
            }
        }



        $participant_member_team_old = ParticipantMemberTeam::where('participant_id', $participant->id)->get();
        foreach ($participant_member_team_old as $pmto) {
            foreach ($participant_member_id as $pmi) {
                $pmto->update([
                    "participant_member_id" => $pmi->id
                ]);
            }
        }

        return "ok";
    }

    protected function validation($parameters)
    {
        return [
            'participant_id' => 'required|integer',
            'user_id' => 'required|array'
        ];
    }
}
