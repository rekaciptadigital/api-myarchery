<?php

namespace App\BLoC\App\ArcheryEvent;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ParticipantMemberTeam;
use App\Models\TemporaryParticipantMember;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Carbon;
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
            throw new BLoCException("participant not found");
        }

        if ($participant->user_id != $user->id) {
            throw new BLoCException("you are not owned this participant");
        }
        
        $user_ids = $parameters->get('user_id');

        $event_category_detail = ArcheryEventCategoryDetail::find($participant->event_category_id);
        if (!$event_category_detail) {
            throw new BLoCException("event category not found");
        }

        $now = Carbon::now('Asia/jakarta');
        $new_format = Carbon::parse($event_category_detail->start_event, new DateTimeZone('Asia/jakarta'));
        
        $gender_category = $event_category_detail->gender_category;

        if ($gender_category == 'mix') {
            if (count($user_ids) != 2 && count($user_ids) != 4) {
                throw new BLoCException("total participants do not meet the requirements");
            }

            $male = [];
            $female = [];

            foreach ($user_ids as $uid) {
                $user = User::find($uid);
                if (!$user) {
                    throw new BLoCException('user not found');
                }

                if ($user->gender ==  'male') {
                    array_push($male, $uid);
                } else {
                    array_push($female, $uid);
                }
            }

            if (count($male) != count($female)) {
                throw new BLoCException("the total number of male and female participants must be the same");
            }
        } else {
            if (count($user_ids) < 3 || count($user_ids) > 5) {
                throw new BLoCException("total participants do not meet the requirements");
            }
        }

        $participant_member_team_old = ParticipantMemberTeam::where('participant_id', $participant->id)->get();
        foreach ($participant_member_team_old as $pmto) {
            $pmto->delete();
        }

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
            }

            array_push($participant_member_id, $participant_member_old);
            $participant_member_team = ParticipantMemberTeam::where('participant_member_id', $participant_member_old->id)->where('event_category_id', $event_category_detail->id)->first();

            if ($participant_member_team) {
                throw new BLoCException("user with email " . $user_register->email . " already join this category");
            }
        }

        foreach ($participant_member_id as $pmi) {
            ParticipantMemberTeam::insertParticipantMemberTeam($participant, $pmi, $event_category_detail);
        }

        $participant['members'] = User::join('archery_event_participant_members', 'archery_event_participant_members.user_id', '=', 'users.id')
            ->join('participant_member_teams', 'participant_member_teams.participant_member_id', '=', 'archery_event_participant_members.id')
            ->where('participant_member_teams.participant_id', $participant->id)
            ->get(['users.*']);

        return $participant;
    }

    protected function validation($parameters)
    {
        return [
            'participant_id' => 'required|integer',
            'user_id' => 'required|array'
        ];
    }
}
