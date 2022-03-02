<?php

namespace App\BLoC\App\ArcheryEvent;

use App\Models\ArcheryClub;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ClubMember;
use App\Models\ParticipantMemberTeam;
use App\Models\TemporaryParticipantMember;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use DateTimeZone;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UpdateParticipantMember extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        // tangkap user login
        $user = Auth::guard('app-api')->user();

        $user_ids = $parameters->get('user_id');

        $team_name = $parameters->get('team_name');

        // tangkap semua data yang dikirimkan dari frontend
        $club_id = $parameters->get('club_id');
        $participant_id = $parameters->get('participant_id');


        // tangkap data yang daftar
        $participant = ArcheryEventParticipant::find($participant_id);
        if (!$participant) {
            throw new BLoCException("participant not found");
        }

        if ($participant->status != 1) {
            throw new BLoCException("anda belum terdaftar di category ini");
        }

        // cek apakah yang daftar sama dengan yang login
        if ($participant->user_id != $user->id) {
            throw new BLoCException("kamu tidak pemilik participant ini");
        }

        // tangkap club yang dikirim dari frontend
        if ($club_id != 0) {
            // cek apakah user tersebut tergabung didalam club sesuai data
            $club_member = ClubMember::where('club_id', $club_id)->where('user_id', $user->id)->first();
            if (!$club_member) {
                throw new BLoCException("you are not joined this club");
            }
        }

        // tangkap event category detail dari yang diikuti users
        $event_category_detail = ArcheryEventCategoryDetail::find($participant->event_category_id);
        if (!$event_category_detail) {
            throw new BLoCException("event category not found");
        }

        $now = Carbon::now();
        $new_format = Carbon::parse($event_category_detail->start_event, new DateTimeZone('Asia/jakarta'));

        // if ($new_format->diffInHours($now) < 24) {
        //     throw new BLoCException("tidak dapat mengubah peserta, minimal mengubah peserta adalah 24 jam sebelum berlangsungnya event");
        // }

        // if ($now > $new_format) {
        //     throw new BLoCException("event telah lewat");
        // }



        if ($event_category_detail->category_team == ArcheryEventCategoryDetail::INDIVIDUAL_TYPE) {
            return $this->updateIndividu($participant, $club_id, $user);
        } else {
            Validator::make($parameters->all(), [
                "user_id" => "required|array",
                "team_name" => "required|string"
            ])->validate();
            return $this->updateTeam($participant, $user_ids, $club_id, $team_name, $event_category_detail, $user);
        }
    }

    private function updateIndividu($participant, $club_id, $user_login)
    {
        $category_id = $participant->event_category_id;
        $event_category_detail = ArcheryEventCategoryDetail::find($category_id);

        $category_detai_team = ArcheryEventCategoryDetail::where('event_id', $event_category_detail->event_id)
            ->where('age_category_id', $event_category_detail->age_category_id)
            ->where('competition_category_id', $event_category_detail->competition_category_id)
            ->where('distance_id', $event_category_detail->distance_id)
            ->where(function ($query) use ($user_login) {
                return $query->where('team_category_id', $user_login->gender . "_team")->orWhere('team_category_id', 'mix_team');
            })
            ->get();

        $participant_memmber = ArcheryEventParticipantMember::where('archery_event_participant_id', $participant->id)->first();
        if (!$participant_memmber) {
            throw new BLoCException("participant_member not found");
        }

        if ($participant->club_id != $club_id) {
            if ($category_detai_team->count() > 0) {
                foreach ($category_detai_team as $cdt) {
                    $participant_member_team = ParticipantMemberTeam::where('event_category_id', $cdt->id)
                        ->where('participant_member_id', $participant_memmber->id)
                        ->first();

                    if ($participant_member_team) {
                        throw new BLoCException("tidak dapat mengubah club karena anda telah terdaftar di team");
                    }
                }
            }
        }

        $participant->club_id = $club_id;
        $participant->save();

        $members = User::join('archery_event_participant_members', 'archery_event_participant_members.user_id', '=', 'users.id')
            ->join('participant_member_teams', 'participant_member_teams.participant_member_id', '=', 'archery_event_participant_members.id')
            ->where('participant_member_teams.participant_id', $participant->id)
            ->get(['users.*']);

        $club = ArcheryClub::find($club_id);

        $output = [];
        $output['participant'] = [
            "participant_id" => $participant->id,
            "user_id" => $participant->user_id,
            "name" => $participant->name,
            "email" => $participant->email,
            "phoneNumber" => $participant->phone_number,
            "age" => $participant->age
        ];
        $output['category_detai'] = $event_category_detail ? $event_category_detail->getCategoryDetailById($category_id) : null;
        $output['members'] = $members;
        $output['club'] = $club;

        return $output;
    }

    private function updateTeam($participant, $user_ids, $club_id, $team_name, $event_category_detail, $user)
    {
        // cek apakah club yang diinputkan terdapat di db
        $club = ArcheryClub::find($club_id);
        if (!$club) {
            throw new BLoCException("club not found");
        }

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

        $participant_member_id = [];

        foreach ($user_ids as $user_id) {
            // cek apakah id user tersebut terdapat di db
            $user_register = User::find($user_id);
            if (!$user_register) {
                throw new BLoCException("user not found");
            }

            // cek apakah user tersebut tergabung di anggota club tersebut
            $club_member = ClubMember::where('club_id', $club->id)->where('user_id', $user_register->id)->first();
            if (!$club_member) {
                throw new BLoCException("member with email " . $user_register->email . " not joined this club");
            }

            // ambil category yang satu grup dengan category detail
            $category = ArcheryEventCategoryDetail::where('event_id', $event_category_detail->event_id)
                ->where('age_category_id', $event_category_detail->age_category_id)
                ->where('competition_category_id', $event_category_detail->competition_category_id)
                ->where('distance_id', $event_category_detail->distance_id)
                ->where('team_category_id', $gender_category == 'mix' ? 'individu ' . $user_register->gender : 'individu ' . $gender_category)
                ->first();

            if (!$category) {
                throw new BLoCException("category individual not found for this category");
            }

            // ambil participant yang user_id sesuai dengan yang diinputkan dan category terdapat di category individu dan club sesuai dengan club baru 
            // ambi participant member sesuai participant yang telah di filter
            $participant_member_old = ArcheryEventParticipant::join('archery_event_participant_members', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
                ->where('archery_event_participants.event_category_id', $category->id)
                ->where('archery_event_participants.user_id', $user_id)
                ->where('archery_event_participants.club_id', $club->id)
                ->get(['archery_event_participant_members.*'])
                ->first();


            if (!$participant_member_old) {
                if ($user->id == $user_id) {
                    throw new BLoCException("you are not joined individual category for this category with this club");
                }
                throw new BLoCException("user with email " . $user_register->email . " not joined individual category for this category with this club");
            }

            $temporary = TemporaryParticipantMember::join('archery_event_participant_members', 'archery_event_participant_members.id', '=', 'temporrary_members.participant_member_id')
                ->join('archery_event_participants', 'archery_event_participants.id', '=', 'temporrary_members.participant_id')
                ->join('transaction_logs', 'transaction_logs.id', '=', 'archery_event_participants.transaction_log_id')
                ->where('temporrary_members.participant_member_id', $participant_member_old->id)
                ->where('temporrary_members.event_category_id', $event_category_detail->id)
                ->get(['transaction_logs.*']);

            if ($temporary->count() > 0) {
                foreach ($temporary as $t) {
                    if ($t->status == 4 && $t->expired_time > time()) {
                        throw new BLoCException("user dengan email " . $user_register->email . " telah didaftarkan pada category ini sebelumnya");
                    }
                }
            }

            array_push($participant_member_id, $participant_member_old);

            if ($participant->club_id != $club->id) {
                $participant_member_team = ParticipantMemberTeam::where('participant_member_id', $participant_member_old->id)->where('event_category_id', $event_category_detail->id)->first();

                if ($participant_member_team) {
                    throw new BLoCException("user with email " . $user_register->email . " already join this category");
                }

                $participant_for_check_on_club_destination = ArcheryEventParticipant::where('user_id', $user_register->id)
                    ->where('event_category_id', $category->id)
                    ->where('club_id', $club_id)->first();

                if ($participant_for_check_on_club_destination) {
                    $participant_memeber_for_check_on_club_destination = ArcheryEventParticipantMember::where('archery_event_participant_id', $participant_for_check_on_club_destination->id)->first();
                    if ($participant_memeber_for_check_on_club_destination) {
                        $participant_member_team_for_check =  ParticipantMemberTeam::where('participant_member_id', $participant_memeber_for_check_on_club_destination->id)
                            ->where('event_category_id', $event_category_detail->id)
                            ->first();
                        if ($participant_member_team_for_check) {
                            throw new BLoCException('user dengan email ' . $user_register->email . ' telah tergabung pada categori ini pada club tersebut');
                        }
                    }
                }
            }
        }

        $participant_member_team_old = ParticipantMemberTeam::where('participant_id', $participant->id)->get();
        foreach ($participant_member_team_old as $pmto) {
            $pmto->delete();
        }

        $participant->club_id = $club->id;
        $participant->team_name = $team_name;
        $participant->save();

        foreach ($participant_member_id as $pmi) {
            ParticipantMemberTeam::saveParticipantMemberTeam($event_category_detail->id, $participant->id, $pmi->id, $event_category_detail->category_team);
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
            'club_id' => 'required|integer'
        ];
    }
}
