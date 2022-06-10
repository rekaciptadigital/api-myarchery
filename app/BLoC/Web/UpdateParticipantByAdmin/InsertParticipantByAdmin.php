<?php

namespace App\BLoC\Web\UpdateParticipantByAdmin;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipantMemberNumber;
use App\Models\ArcheryEventParticipantNumber;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\ArcheryEventQualificationTime;
use App\Models\ParticipantMemberTeam;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class InsertParticipantByAdmin extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $category_id = $parameters->get("category_id");
        $emails = $parameters->get("emails");

        $category = ArcheryEventCategoryDetail::find($category_id);
        if (!$category) {
            throw new BLoCException("category not found");
        }

        $qualification_time = ArcheryEventQualificationTime::where('category_detail_id', $category_id)->first();
        if (!$qualification_time) {
            throw new BLoCException('event belum bisa di daftar');
        }

        $event = ArcheryEvent::find($category->event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        // cek waktu pendaftaran sudah berakhir atau belum
        $carbon_registration_start_datetime = Carbon::parse($event->registration_start_datetime);
        $carbon_registration_end_datetime = Carbon::parse($event->registration_end_datetime);

        $carbon_registration_start_date = Carbon::create($carbon_registration_start_datetime->year, $carbon_registration_start_datetime->month, $carbon_registration_start_datetime->day, 0, 0, 0);
        $carbon_registration_end_date = Carbon::create($carbon_registration_end_datetime->year, $carbon_registration_end_datetime->month, $carbon_registration_end_datetime->day, 0, 0, 0);


        $check = Carbon::today()->between($carbon_registration_start_date, $carbon_registration_end_date);

        if (!$check) {
            // throw new BLoCException("waktu pendaftaran tidak sesuai dengan periode pendaftaran");
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException("forbiden");
        }

        foreach ($emails as $key => $value) {
            $user = User::where("email", $value)->first();
            if (!$user) {
                throw new BLoCException("user with email " . $value . "not found");
            }

            // hitung jumlah participant pada category yang didaftarkan user
            $participant_count = ArcheryEventParticipant::countEventUserBooking($category_id);

            if ($participant_count >= $category->quota) {
                $msg = "quota kategori ini sudah penuh";
                // check kalo ada pembayaran yang pending
                $participant_count_pending = ArcheryEventParticipant::join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                    ->where("event_category_id", $category_id)
                    ->where("transaction_logs.status", 4)->where("transaction_logs.expired_time", ">", time())
                    ->where("event_id", $event->id)->count();

                if ($participant_count_pending > 0) {
                    $msg = "untuk sementara  " . $msg . ", silahkan coba beberapa saat lagi";
                } else {
                    $msg = $msg . ", silahkan daftar di kategori lain";
                }
                throw new BLoCException($msg);
            }

            // cek jika memiliki syarat max umur
            if ($category->max_age != 0) {
                if ($user->age == null) {
                    throw new BLoCException("tgl lahir anda belum di set");
                }
                $check_date = $this->getAge($user->date_of_birth, $event->event_start_datetime);
                // cek apakah usia user memenuhi syarat categori event
                if ($check_date["y"] > $category->max_age) {
                    // throw new BLoCException("tidak memenuhi syarat usia, syarat maksimal usia adalah " . $category->max_age . " tahun");
                }
                if ($check_date["y"] == $category->max_age && ($check_date["m"] > 0 || $check_date["d"] > 0)) {
                    // throw new BLoCException("tidak memenuhi syarat usia, syarat maksimal usia adalah " . $category->max_age . " tahun");
                }
            }

            // cek jika memiliki syarat minimal umur
            if ($category->min_age != 0) {
                if ($user->age == null) {
                    throw new BLoCException("tgl lahir anda belum di set");
                }
                $check_date = $this->getAge($user->date_of_birth, $event->event_start_datetime);
                // cek apakah usia user memenuhi syarat categori event
                $check_date = $this->getAge($user->date_of_birth, $event->event_start_datetime);
                if ($check_date["y"] < $category->min_age) {
                    throw new BLoCException("tidak memenuhi syarat usia, minimal usia adalah " . $category->min_age . " tahun");
                }
            }

            $gender_category = $category->gender_category;
            if ($user->gender != $gender_category) {
                if (empty($user->gender))
                    throw new BLoCException('silahkan set gender terlebih dahulu, kamu bisa update gender di halaman update profile :) ');

                throw new BLoCException('oops.. kategori ini  hanya untuk gender ' . $gender_category . " dan user dengan email " . $value . " gender tidak sesuai");
            }

            // cek apakah user telah pernah mendaftar di categori tersebut
            $isExist = ArcheryEventParticipant::where('event_category_id', $category_id)
                ->where('user_id', $user->id)->get();
            if ($isExist->count() > 0) {
                foreach ($isExist as $ie) {
                    if ($ie->status == 1) {
                        throw new BLoCException("event dengan kategori ini sudah di ikuti oleh user dengan email " . $value);
                    }
                }
            }

            // insert data participant
            $participant = ArcheryEventParticipant::create([
                'club_id' => 0,
                'user_id' => $user->id,
                'status' => 1,
                'event_id' => $event->id,
                'name' => $user->name,
                'type' => $category->category_team,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'age' => $user->age,
                'gender' => $user->gender,
                'team_category_id' => $category->team_category_id,
                'age_category_id' => $category->age_category_id,
                'competition_category_id' => $category->competition_category_id,
                'distance_id' => $category->distance_id,
                'transaction_log_id' => 0,
                'unique_id' => Str::uuid(),
                'event_category_id' => $category_id,
                "register_by" => 2
            ]);

            // insert ke archery_event_participant_member
            $member = ArcheryEventParticipantMember::create([
                "archery_event_participant_id" => $participant->id,
                "name" => $user->name,
                "gender" => $user->gender,
                "birthdate" => $user->date_of_birth,
                "age" => $user->age,
                "team_category_id" => $category->team_category_id,
                "user_id" => $user->id
            ]);

            ArcheryEventParticipantNumber::saveNumber(ArcheryEventParticipantNumber::makePrefix($category_id, $user->gender), $participant->id);
            ArcheryEventParticipantMemberNumber::saveMemberNumber(ArcheryEventParticipantMemberNumber::makePrefix($event->id, $user->gender), $user->id, $event->id);
            $key = env("REDIS_KEY_PREFIX") . ":qualification:score-sheet:updated";
            Redis::hset($key, $category_id, $category_id);
            ArcheryEventQualificationScheduleFullDay::create([
                'qalification_time_id' => $qualification_time->id,
                'participant_member_id' => $member->id,
            ]);
            ParticipantMemberTeam::saveParticipantMemberTeam($category_id, $participant->id, $member->id, $category->category_team);
        }

        return "success";
    }

    private function getAge($birth_day, $date_check)
    {
        $birthDt = new DateTime($birth_day);
        $date = new DateTime($date_check);
        return [
            "y" => $date->diff($birthDt)->y,
            "m" => $date->diff($birthDt)->m,
            "d" => $date->diff($birthDt)->d
        ];
    }

    protected function validation($parameters)
    {
        return [
            "category_id" => "required|integer",
            "emails"    => "required|array|min:1|max:20",
            "emails.*"  => "required|email|distinct",
        ];
    }
}
