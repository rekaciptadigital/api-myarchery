<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Abstracts\Transactional;
use App\Libraries\PaymentGateWay;
use App\Models\ArcheryEvent;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\ArcheryEventQualificationTime;
use App\Models\ClubMember;
use App\Models\ParticipantMemberTeam;
use App\Models\ArcheryEventParticipantMemberNumber;
use App\Models\TemporaryParticipantMember;
use App\Models\TransactionLog;
use App\Models\User;
use Carbon\Carbon as CarbonCarbon;
use DateTime;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redis;

class AddEventOrder extends Transactional
{
    public function getDescription()
    {
        return "";
        /*
            # order individu
                db insert :
                            - archery_event_participants
                            - archery_event_participant_members
                            - transaction_log (for have fee)
                            - archery_event_participant_member_numbers (if free)
                            - archery_event_qualification_schedule_full_days (if free)
                            - participant_member_team (if free)
        */
    }

    protected function process($parameters)
    {
        $user = Auth::guard('app-api')->user();
        $team_name = $parameters->get('team_name') ? $parameters->get('team_name') : "";
        $event_category_id = $parameters->get('event_category_id');
        $user_id = $parameters->get('user_id');

        // get event_category_detail by id
        $event_category_detail = ArcheryEventCategoryDetail::find($event_category_id);
        if (!$event_category_detail) {
            throw new BLoCException("category event not found");
        }

        // cek waktu pendaftaran sudah berakhir atau belum
        $event = ArcheryEvent::find($event_category_detail->event_id);
        if ($event->registration_end_datetime < Carbon::now()) {
            throw new BLoCException('pendaftaran telah ditutup');
        }

        // cek apakah user sudah tergabung dalam club atau belum
        if ($parameters->get('club_id') != 0) {
            $club_member = ClubMember::where('club_id', $parameters->get('club_id'))->where('user_id', $user->id)->first();
            if (!$club_member) {
                throw new BLoCException("member not joined this club");
            }
        } else {
            $club_member = null;
        }

        if ($event_category_detail->category_team == ArcheryEventCategoryDetail::INDIVIDUAL_TYPE) {
            return $this->registerIndividu($event_category_detail, $user, $club_member, $team_name, $event);
        } else {
            return $this->registerTeamBestOfThree($event_category_detail, $user, $club_member, $team_name);
        }
    }

    private function registerIndividu($event_category_detail, $user, $club_member, $team_name, $event)
    {
        $time_now = time();

        $qualification_time = ArcheryEventQualificationTime::where('category_detail_id', $event_category_detail->id)->first();
        if (!$qualification_time) {
            throw new BLoCException('event belum bisa di daftar');
        }

        // hitung jumlah participant pada category yang didaftarkan user
        $participant_count = ArcheryEventParticipant::countEventUserBooking($event_category_detail->id);

        if ($participant_count >= $event_category_detail->quota) {
            $msg = "quota kategori ini sudah penuh";
            // check kalo ada pembayaran yang pending
            $participant_count_pending = ArcheryEventParticipant::join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                ->where("event_category_id", $event_category_detail->id)
                ->where("transaction_logs.status", 4)->where("transaction_logs.expired_time", ">", $time_now)
                ->where("event_id", $event_category_detail->event_id)->count();

            if ($participant_count_pending > 0) {
                $msg = "untuk sementara  " . $msg . ", silahkan coba beberapa saat lagi";
            } else {
                $msg = $msg . ", silahkan daftar di kategori lain";
            }
            throw new BLoCException($msg);
        }

        // cek jika memiliki syarat max umur

        if ($event_category_detail->max_age != 0) {
            if ($user->age == null) {
                throw new BLoCException("tgl lahir anda belum di set");
            }
            $check_date = $this->getAge($user->date_of_birth, $event->event_start_datetime);
            // cek apakah usia user memenuhi syarat categori event
            if ($check_date["y"] > $event_category_detail->max_age) {
                throw new BLoCException("tidak memenuhi syarat usia, syarat maksimal usia adalah " . $event_category_detail->max_age . " tahun");
            }
            if ($check_date["y"] == $event_category_detail->max_age && ($check_date["m"] > 0 || $check_date["d"] > 0)) {
                throw new BLoCException("tidak memenuhi syarat usia, syarat maksimal usia adalah " . $event_category_detail->max_age . " tahun");
            }
        }

        // cek jika memiliki syarat minimal umur
        if ($event_category_detail->min_age != 0) {
            if ($user->age == null) {
                throw new BLoCException("tgl lahir anda belum di set");
            }
            // cek apakah usia user memenuhi syarat categori event
            if ($user->age < $event_category_detail->min_age) {
                throw new BLoCException("tidak memenuhi syarat usia, minimal usia adalah " . $event_category_detail->min_age . " tahun");
            }
        }

        $gender_category = $event_category_detail->gender_category;
        if ($user->gender != $gender_category) {
            if (empty($user->gender))
                throw new BLoCException('silahkan set gender terlebih dahulu, kamu bisa update gender di halaman update profile :) ');

            throw new BLoCException('oops.. kategori ini  hanya untuk gender ' . $gender_category);
        }

        // cek apakah user telah pernah mendaftar di categori tersebut
        $isExist = ArcheryEventParticipant::where('event_category_id', $event_category_detail->id)
            ->where('user_id', $user->id)->first();
        if ($isExist) {
            if ($isExist->status == 1) {
                throw new BLoCException("event dengan kategori ini sudah di ikuti");
            }
            $isExist_transaction_log = TransactionLog::find($isExist->transaction_log_id);
            if ($isExist_transaction_log) {
                if ($isExist_transaction_log->status == 4 && $isExist_transaction_log->expired_time > time()) {
                    throw new BLoCException("transaksi dengan kategory ini sudah pernah dilakukan, silahkan selesaikan pembayaran");
                }
            }
        }

        // insert data participant
        $participant = ArcheryEventParticipant::insertParticipant($user, Str::uuid(), $team_name, $event_category_detail, 4, $club_member != null ? $club_member->club_id : 0);

        $order_id = env("ORDER_ID_PREFIX", "OE-S") . $participant->id;

        // insert ke archery_event_participant_member
        $member = ArcheryEventParticipantMember::create([
            "archery_event_participant_id" => $participant->id,
            "name" => $user->name,
            "gender" => $user->gender,
            "birthdate" => $user->date_of_birth,
            "age" => $user->age,
            "team_category_id" => $event_category_detail->team_category_id,
            "user_id" => $user->id
        ]);

        if ($event_category_detail->fee < 1) {
            $participant->status = 1;
            $participant->save();
            ArcheryEventParticipantMemberNumber::saveMemberNumber(ArcheryEventParticipantMemberNumber::makePrefix($event_category_detail->event_id, $user->gender), $user->id, $event_category_detail->event_id);
            $key = env("REDIS_KEY_PREFIX") . ":qualification:score-sheet:updated";
            Redis::hset($key,$event_category_id,$event_category_id);
            ArcheryEventQualificationScheduleFullDay::create([
                'qalification_time_id' => $qualification_time->id,
                'participant_member_id' => $member->id,
            ]);
            ParticipantMemberTeam::saveParticipantMemberTeam($event_category_detail->id, $participant->id, $member->id, $event_category_detail->category_team);

            $res = [
                "archery_event_participant_id" => $participant->id,
                "payment_info" => null
            ];
            
            return $this->composeResponse($res);
        }

        $payment = PaymentGateWay::setTransactionDetail((int)$event_category_detail->fee, $order_id)
            ->enabledPayments(["bca_va", "bni_va", "bri_va", "gopay", "other_va"])
            ->setCustomerDetails($user->name, $user->email, $user->phone_number)
            ->addItemDetail($event_category_detail->id, (int)$event_category_detail->fee, $event_category_detail->event_name)
            ->createSnap();

        $participant->transaction_log_id = $payment->transaction_log_id;
        $participant->save();

        $res = [
            "archery_event_participant_id" => $participant->id,
            'payment_info' => $payment
        ];
        return $this->composeResponse($res);
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

    private function registerTeam($event_category_detail, $user, $club_member, $team_name, $user_id)
    {
        // mengambil gender category
        $gender_category = $event_category_detail->gender_category;

        if ($gender_category == 'mix') {
            if (count($user_id) != 2) {
                throw new BLoCException("harus mendaftarkan 2 peserta");
            }

            $male = [];
            $female = [];

            foreach ($user_id as $uid) {
                $user = User::find($uid);
                if (!$user) {
                    throw new BLoCException('user tidak ditemukan');
                }

                if ($user->gender ==  'male') {
                    array_push($male, $uid);
                } else {
                    array_push($female, $uid);
                }
            }

            if (count($male) != count($female)) {
                throw new BLoCException("peserta pada category ini musti putra & putri");
            }
        } else {
            if (count($user_id) != 3) {
                throw new BLoCException("harus mendaftarkan 3 peserta");
            }
        }

        $participant_member_id = [];

        if ($club_member == null) {
            throw new BLoCException("club tidak ditemukan");
        }

        foreach ($user_id as $u) {
            $user_register = User::find($u);
            if (!$user_register) {
                throw new BLoCException("user tidak ditemukan");
            }

            $category = ArcheryEventCategoryDetail::where('event_id', $event_category_detail->event_id)
                ->where('age_category_id', $event_category_detail->age_category_id)
                ->where('competition_category_id', $event_category_detail->competition_category_id)
                ->where('distance_id', $event_category_detail->distance_id)
                ->where('team_category_id', $gender_category == 'mix' ? 'individu ' . $user_register->gender : 'individu ' . $gender_category)
                ->first();

            // cek apakah terdapat category individual
            if (!$category) {
                throw new BLoCException("kategori tidak ditemukan");
            }

            $participant_member_old = ArcheryEventParticipant::join('archery_event_participant_members', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
                ->where('archery_event_participants.event_category_id', $category->id)
                ->where('archery_event_participants.user_id', $u)
                ->where('archery_event_participants.club_id', $club_member->club_id)
                ->get(['archery_event_participant_members.*'])
                ->first();

            if (!$participant_member_old) {
                if ($user->id == $u) {
                    throw new BLoCException("user belum mendaftar event individual");
                }
                throw new BLoCException("user dengan email " . $user_register->email . " belum mengikuti kategori individu");
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
            $participant_member_team = ParticipantMemberTeam::where('participant_member_id', $participant_member_old->id)->where('event_category_id', $event_category_detail->id)->first();
            if ($participant_member_team) {
                throw new BLoCException("user dengan " . $user_register->email . " telah tergabung di category ini");
            }
        }

        $participant_new = ArcheryEventParticipant::insertParticipant($user, Str::uuid(), $team_name, $event_category_detail, 4, $club_member->club_id);

        if ($event_category_detail->fee < 1) {
            $participant_new->status = 1;
            $participant_new->save();

            foreach ($user_id as $u) {
                $participant_old = ArcheryEventParticipant::where('event_category_id', $category->id)->where('user_id', $u)->first();
                if (!$participant_old) {
                    throw new BLoCException("data participant old not found");
                }
                $participant_member_old = ArcheryEventParticipantMember::where('user_id', $u)->where('archery_event_participant_id', $participant_old->id)->first();
                if ($participant_member_old) {
                    ParticipantMemberTeam::saveParticipantMemberTeam($event_category_detail->id, $participant_old->id, $participant_member_old->id, $event_category_detail->category_team);
                } else {
                    throw new BLoCException("this user not participant for category individual");
                }
            }
            $res = [
                "archery_event_participant_id" => $participant_new->id,
                "payment_info" => null
            ];
            return $this->composeResponse($res);
        }


        $order_id = env("ORDER_ID_PREFIX", "OE-S") . $participant_new->id;
        $payment = PaymentGateWay::setTransactionDetail((int)$event_category_detail->fee, $order_id)
            ->enabledPayments(["bca_va", "bni_va", "bri_va", "gopay", "other_va"])
            ->setCustomerDetails($user->name, $user->email, $user->phone_number)
            ->addItemDetail($event_category_detail->id, (int)$event_category_detail->fee, $event_category_detail->event_name)
            ->createSnap();

        foreach ($participant_member_id as $pm) {
            TemporaryParticipantMember::create([
                'user_id' => $pm->user_id,
                'participant_member_id' => $pm->id,
                'participant_id' => $participant_new->id,
                'event_category_id' => $event_category_detail->id
            ]);
        }
        $participant_new->transaction_log_id = $payment->transaction_log_id;
        $participant_new->save();
        $res = [
            "archery_event_participant_id" => $participant_new->id,
            "payment_info" => $payment
        ];
        return $this->composeResponse($res);
    }

    private function registerTeamBestOfThree($event_category_detail, $user, $club_member, $team_name)
    {
        // mengambil gender category

        $gender_category = $event_category_detail->gender_category;
        $time_now = time();

        $check_register_same_category = ArcheryEventParticipant::where('archery_event_participants.event_category_id', $event_category_detail->id)
        ->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
        ->where('archery_event_participants.club_id', $club_member->club_id)
        ->where(function ($query) use ($time_now) {
            $query->where("archery_event_participants.status", 1);
            $query->orWhere(function ($q) use ($time_now) {
                $q->where("archery_event_participants.status", 4);
                $q->where("transaction_logs.expired_time", ">", $time_now);
            });
        })
        ->count();
    
        if ($gender_category == 'mix') {
            if($check_register_same_category >= 2){
                $check_panding = ArcheryEventParticipant::where('archery_event_participants.event_category_id', $event_category_detail->id)
                ->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                ->where('archery_event_participants.club_id', $club_member->club_id)
                ->where("archery_event_participants.status", 4)
                ->where("transaction_logs.expired_time", ">", $time_now)
                ->count();
                if($check_panding > 0)
                    throw new BLoCException("ada transaksi yang belum diselesaikan oleh club pada category ini");
                else
                    throw new BLoCException("club anda sudah terdaftar 2 kali di kategory ini");
            }
        } else {
            if($check_register_same_category >= 2){
                $check_panding = ArcheryEventParticipant::where('archery_event_participants.event_category_id', $event_category_detail->id)
                ->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                ->where('archery_event_participants.club_id', $club_member->club_id)
                ->where("archery_event_participants.status", 4)
                ->where("transaction_logs.expired_time", ">", $time_now)
                ->count();
                if($check_panding > 0)
                    throw new BLoCException("ada transaksi yang belum diselesaikan oleh club pada category ini");
                else
                    throw new BLoCException("club anda sudah terdaftar 2 kali di kategory ini");
            }
            $team_category_id = $event_category_detail->team_category_id == "female_team" ? "individu female" : "individu male";
            $check_individu_category_detail = ArcheryEventCategoryDetail::where('event_id', $event_category_detail->event_id)
                ->where('age_category_id', $event_category_detail->age_category_id)
                ->where('competition_category_id', $event_category_detail->competition_category_id)
                ->where('distance_id', $event_category_detail->distance_id)
                ->where('team_category_id', $team_category_id)
                ->first();

            $check_participant = ArcheryEventParticipant::join('archery_event_participant_members', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
                ->where('archery_event_participants.event_category_id', $check_individu_category_detail->id)
                ->where('archery_event_participants.club_id', $club_member->club_id)
                ->count();
            if($check_participant < (($check_register_same_category + 1) * 3)){
                throw new BLoCException("untuk pendaftaran ke ".($check_register_same_category +1)." minimal harus ada ".(($check_register_same_category + 1) * 3)." peserta tedaftar dengan club ini");
            }
        }

        $participant_new = ArcheryEventParticipant::insertParticipant($user, Str::uuid(), $team_name, $event_category_detail, 4, $club_member->club_id);

        if ($event_category_detail->fee < 1) {
            $participant_new->status = 1;
            $participant_new->save();

            $res = [
                "archery_event_participant_id" => $participant_new->id,
                "payment_info" => null
            ];
            return $this->composeResponse($res);
        }

        $order_id = env("ORDER_ID_PREFIX", "OE-S") . $participant_new->id;
        $payment = PaymentGateWay::setTransactionDetail((int)$event_category_detail->fee, $order_id)
            ->enabledPayments(["bca_va", "bni_va", "bri_va", "gopay", "other_va"])
            ->setCustomerDetails($user->name, $user->email, $user->phone_number)
            ->addItemDetail($event_category_detail->id, (int)$event_category_detail->fee, $event_category_detail->event_name)
            ->createSnap();
        $participant_new->transaction_log_id = $payment->transaction_log_id;
        $participant_new->save();
        $res = [
            "archery_event_participant_id" => $participant_new->id,
            "payment_info" => $payment
        ];
        return $this->composeResponse($res);
    }

    protected function validation($parameters)
    {
        return [
            "event_category_id" => "required",
            "club_id" => "required",
        ];
    }

    private function composeResponse(array $res)
    {
        return [
            "archery_event_participant_id" => $res["archery_event_participant_id"],
            "payment_info" => $res["payment_info"]
        ];
    }
}
