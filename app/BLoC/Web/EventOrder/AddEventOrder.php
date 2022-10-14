<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantNumber;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Abstracts\Transactional;
use App\Libraries\PaymentGateWay;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventEmailWhiteList;
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
use App\Models\ArcherySeriesUserPoint;
use DateTime;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redis;

class AddEventOrder extends Transactional
{
    var $gateway = "";
    var $have_fee_payment_gateway = false;
    var $payment_methode = "";
    var $myarchery_fee = 0;
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
        $this->payment_methode = $parameters->get('payment_methode') ? $parameters->get('payment_methode') : "bankTransfer";
        
        $user = Auth::guard('app-api')->user();
        if ($user->checkIsCompleteUserData() != 1) {
            throw new BLoCException("data user belum lengkap, harap lengkapi data");
        }

        $event_category_id = $parameters->get('event_category_id');
        $day_choice = $parameters->get("day_choice");
        $club_id = $parameters->get("club_id");
        $this->gateway = $parameters->get("gateway") ? $parameters->get("gateway") : env("PAYMENT_GATEWAY","midtrans");

        // get event_category_detail by id
        $event_category_detail = ArcheryEventCategoryDetail::find($event_category_id);
        if (!$event_category_detail) {
            throw new BLoCException("category event not found");
        }

        $data = Redis::get($event_category_detail->id . "_LIVE_SCORE");
        if ($data) {
            Redis::del($event_category_detail->id . "_LIVE_SCORE");
        }

        $with_early_bird = 0;
        // cek harga apakah normal atau early bird
        if ($user->is_wna == 0) {
            $price = $event_category_detail->fee == null ? 0 : $event_category_detail->fee;
            if ($event_category_detail->is_early_bird == 1) {
                $price = $event_category_detail->early_bird;
                $with_early_bird = 1;
            }
        } else {
            $price = $event_category_detail->normal_price_wna;
            if ($event_category_detail->getIsEarlyBirdWna() == 1) {
                $price = $event_category_detail->early_price_wna;
                $with_early_bird = 1;
            }
        }

        $is_marathon = 0;
        $event = ArcheryEvent::find($event_category_detail->event_id);
        if (!$event) {
            throw new BLoCException("event tidak tersedia");
        }

        if($event->my_archery_fee_percentage > 0)
            $this->myarchery_fee = round($price * ($event->my_archery_fee_percentage/100));
        
        $this->have_fee_payment_gateway = $event->include_payment_gateway_fee_to_user > 0 ? true : false;

        if ($event->is_private) {
            $check_email_whitelist = ArcheryEventEmailWhiteList::where("email", $user->email)->where("event_id", $event->id)->first();
            if (!$check_email_whitelist)
                throw new BLoCException("Mohon maaf akun anda tidak terdaftar sebagai peserta");
        }

        if ($event->event_type == "Marathon") {
            $is_marathon = 1;
            Validator::make($parameters->all(), ["day_choice" => "required|date"])->validate();
        }

        // cek apakah event butuh verifikasi user atau tidak
        if ($event->need_verify == 1) {
            if ($user->verify_status != 1) {
                throw new BLoCException("akun anda belum terverifikasi");
            }
        }

        $now = time();
        if (!$event->registration_start_datetime || !$event->registration_end_datetime) {
            throw new BLoCException("tanggal pendaftaran default belum di set");
        }

        $time_register_event_start = strtotime($event->registration_start_datetime);
        $time_register_event_end = strtotime($event->registration_end_datetime);

        if ($event_category_detail->start_registration && $event_category_detail->end_registration) {
            $time_registration_start_category = strtotime($event_category_detail->start_registration);
            $time_registration_end_category = strtotime($event_category_detail->end_registration);
        }

        if (!isset($time_registration_start_category) || !isset($time_registration_end_category)) {
            if (($now < $time_register_event_start) || ($now > $time_register_event_end)) {
                throw new BLoCException("waktu pendaftaran tidak sesuai dengan periode pendaftaran event");
            }
        } else {
            if (($now < $time_registration_start_category) || ($now > $time_registration_end_category)) {
                throw new BLoCException("waktu pendaftaran tidak sesuai dengan periode pendaftaran untuk category ini");
            }
        }


        if (($parameters->get("with_club") == "yes") && ($parameters->get("club_id") == 0)) {
            throw new BLoCException("club harus diisi");
        }



        // cek apakah user sudah tergabung dalam club atau belum
        $club_member = null;
        if ($club_id != 0) {
            $club_member = ClubMember::where('club_id', $club_id)->where('user_id', $user->id)->first();
            if (!$club_member) {
                throw new BLoCException("member not joined this club");
            }
        }

        if ($event_category_detail->category_team == ArcheryEventCategoryDetail::INDIVIDUAL_TYPE) {
            return $this->registerIndividu($event_category_detail, $user, $club_member, $event, $price, $is_marathon, $day_choice, $with_early_bird);
        } else {
            return $this->registerTeamBestOfThree($event_category_detail, $user, $club_member, $price, $with_early_bird);
        }
    }

    private function registerIndividu($event_category_detail, $user, $club_member, $event, $price, $is_marathon, $day_choice, $with_early_bird)
    {
        $time_now = time();


        $qualification_time = ArcheryEventQualificationTime::where('category_detail_id', $event_category_detail->id)->first();
        if (!$qualification_time) {
            throw new BLoCException('event belum bisa di daftar');
        }

        if ($is_marathon == 1) {
            $day_choice = date('Y-m-d', strtotime($day_choice));
            $category_start = date('Y-m-d', strtotime($qualification_time->event_start_datetime));
            $category_end = date('Y-m-d', strtotime($qualification_time->event_end_datetime));

            if (($day_choice < $category_start) || ($day_choice > $category_end)) {
                throw new BLoCException("inputan tanggal tidak sesuai");
            }
        }

        // cek apakah user telah booking
        $participant = ArcheryEventParticipant::where("user_id", $user->id)
            ->where("status", 6)
            ->where("expired_booking_time", ">", time())
            ->where("event_category_id", $event_category_detail->id)
            ->first();

        // hitung jumlah participant pada category yang didaftarkan user
        $participant_count = ArcheryEventParticipant::countEventUserBooking($event_category_detail->id);
        if ($participant_count > $event_category_detail->quota) {
            $msg = "quota kategori ini sudah penuh";
            // check kalo ada pembayaran yang pending
            $participant_count_pending = ArcheryEventParticipant::join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                ->where("event_category_id", $event_category_detail->id)
                ->where("archery_event_participants.status", 4)
                ->where("transaction_logs.status", 4)
                ->where("transaction_logs.expired_time", ">", $time_now)
                ->where("event_id", $event_category_detail->event_id)->count();

            if ($participant_count_pending > 0) {
                $msg = "untuk sementara  " . $msg . ", silahkan coba beberapa saat lagi";
            } else {
                $msg = $msg . ", silahkan daftar di kategori lain";
            }
            throw new BLoCException($msg);
        }


        if ($event_category_detail->is_age == 1) {
            // cek jika memiliki syarat max umur
            if ($event_category_detail->max_age > 0) {
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
            if ($event_category_detail->min_age > 0) {
                if ($user->age == null) {
                    throw new BLoCException("tgl lahir anda belum di set");
                }
                $check_date = $this->getAge($user->date_of_birth, $event->event_start_datetime);
                // cek apakah usia user memenuhi syarat categori event
                $check_date = $this->getAge($user->date_of_birth, $event->event_start_datetime);
                if ($check_date["y"] < $event_category_detail->min_age) {
                    throw new BLoCException("tidak memenuhi syarat usia, minimal usia adalah " . $event_category_detail->min_age . " tahun");
                }
            }
        } else {
            // cek jika ada persyaratan tanggal minimal kelahiran
            if ($event_category_detail->min_date_of_birth != null) {
                if (strtotime($user->date_of_birth) < strtotime($event_category_detail->min_date_of_birth)) {
                    throw new BLoCException("tidak memenuhi syarat kelahiran, syarat kelahiran minimal adalah " . date("Y-m-d", strtotime($event_category_detail->min_date_of_birth)));
                }
            }

            if ($event_category_detail->max_date_of_birth != null) {
                if (strtotime($user->date_of_birth) > strtotime($event_category_detail->max_date_of_birth)) {
                    throw new BLoCException("tidak memenuhi syarat kelahiran, syarat kelahiran maksimal adalah " . date("Y-m-d", strtotime($event_category_detail->max_date_of_birth)));
                }
            }
        }

        $gender_category = $event_category_detail->gender_category;
        if ($event->event_type == "Full_day") {
            if ($user->gender != $gender_category) {
                if (empty($user->gender)) {
                    throw new BLoCException('silahkan set gender terlebih dahulu, kamu bisa update gender di halaman update profile :) ');
                }

                if ($gender_category != "mix") {
                    throw new BLoCException('oops.. kategori ini  hanya untuk gender ' . $gender_category);
                }
            }
        }
        // cek apakah user telah pernah mendaftar di categori tersebut
        $isExist = ArcheryEventParticipant::where('event_category_id', $event_category_detail->id)
            ->where('user_id', $user->id)
            ->get();
        if ($isExist->count() > 0) {
            foreach ($isExist as $ie) {
                if ($ie->status == 1) {
                    throw new BLoCException("event dengan kategori ini sudah di ikuti");
                }

                if ($ie->status == 4) {
                    $ie_transaction_log = TransactionLog::find($ie->transaction_log_id);
                    if ($ie_transaction_log) {
                        if ($ie_transaction_log->status == 4 && $ie_transaction_log->expired_time > time()) {
                            throw new BLoCException("transaksi dengan kategory ini sudah pernah dilakukan, silahkan selesaikan pembayaran");
                        }
                    }
                }
            }
        }

        if ($participant) {
            $participant->status = 4;
            $participant->club_id = $club_member != null ? $club_member->club_id : 0;
            $participant->is_early_bird_payment = $with_early_bird;
            $participant->save();
        } else {
            // insert data participant
            $participant = ArcheryEventParticipant::insertParticipant($user, Str::uuid(), $event_category_detail, 4, $club_member != null ? $club_member->club_id : 0, $is_marathon == 1 ? $day_choice : null, 0, $with_early_bird);
        }

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

        if ($price < 1) {
            $participant->status = 1;
            $participant->save();
            ArcheryEventParticipantNumber::saveNumber(ArcheryEventParticipantNumber::makePrefix($event_category_detail->id, $user->gender), $participant->id);
            ArcheryEventParticipantMemberNumber::saveMemberNumber(ArcheryEventParticipantMemberNumber::makePrefix($event_category_detail->event_id, $user->gender), $user->id, $event_category_detail->event_id);
            $key = env("REDIS_KEY_PREFIX") . ":qualification:score-sheet:updated";
            Redis::hset($key, $event_category_detail->id, $event_category_detail->id);
            ArcheryEventQualificationScheduleFullDay::create([
                'qalification_time_id' => $qualification_time->id,
                'participant_member_id' => $member->id,
            ]);
            ParticipantMemberTeam::saveParticipantMemberTeam($event_category_detail->id, $participant->id, $member->id, $event_category_detail->category_team);

            $res = [
                "archery_event_participant_id" => $participant->id,
                "payment_info" => null
            ];
            ArcherySeriesUserPoint::setAutoUserMemberCategory($event_category_detail->event_id, $user->id);
            return $this->composeResponse($res);
        }

        $payment = PaymentGateWay::setTransactionDetail((int)$price, $order_id)
            ->setGateway($this->gateway)
            ->setCustomerDetails($user->name, $user->email, $user->phone_number)
            ->addItemDetail($event_category_detail->id, (int)$price, $event_category_detail->event_name)
            ->feePaymentsToUser($this->have_fee_payment_gateway)
            ->setMyarcheryFee($this->myarchery_fee)
            ->createSnap();
        if(!$payment->status)
            throw new BLoCException($payment->message);

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

    private function registerTeam($event_category_detail, $user, $club_member, $team_name, $user_id, $price)
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

        $participant_new = ArcheryEventParticipant::insertParticipant($user, Str::uuid(), $team_name, $event_category_detail, 4, $club_member->club_id, null, 0);

        if ($price < 1) {
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
        $payment = PaymentGateWay::setTransactionDetail((int)$price, $order_id)
            ->setGateway($this->gateway)
            ->setCustomerDetails($user->name, $user->email, $user->phone_number)
            ->addItemDetail($event_category_detail->id, (int)$price, $event_category_detail->event_name)
            ->feePaymentsToUser($this->have_fee_payment_gateway)
            ->setMyarcheryFee($this->myarchery_fee)
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

    private function registerTeamBestOfThree($event_category_detail, $user, $club_member, $price, $with_early_bird)
    {
        // mengambil gender category

        $gender_category = $event_category_detail->gender_category;
        $time_now = time();

        $participant = ArcheryEventParticipant::where("user_id", $user->id)
            ->where("status", 6)
            ->where("expired_booking_time", ">", time())
            ->where("event_category_id", $event_category_detail->id)
            ->first();

        // cek total pendaftar yang masih pending dan sukses
        $check_register_same_category = ArcheryEventParticipant::where('archery_event_participants.event_category_id', $event_category_detail->id)
            ->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
            ->where('archery_event_participants.club_id', $club_member->club_id)
            ->where(function ($query) use ($time_now) {
                $query->where("archery_event_participants.status", 1);
                $query->orWhere(function ($q) use ($time_now) {
                    $q->where("archery_event_participants.status", 4);
                    $q->where("transaction_logs.expired_time", ">", $time_now);
                });
            })->count();

        if ($gender_category == 'mix') {
            // if ($check_register_same_category >= 2) {
            //     $check_panding = ArcheryEventParticipant::where('archery_event_participants.event_category_id', $event_category_detail->id)
            //         ->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
            //         ->where('archery_event_participants.club_id', $club_member->club_id)
            //         ->where("archery_event_participants.status", 4)
            //         ->where("transaction_logs.expired_time", ">", $time_now)
            //         ->count();

            //     if ($check_panding > 0) {
            //         throw new BLoCException("ada transaksi yang belum diselesaikan oleh club pada category ini");
            //     } else {
            //         throw new BLoCException("club anda sudah terdaftar 2 kali di kategory ini");
            //     }
            // } else {
            //     ArcheryEventParticipant::checkParticipantMixteamOrder($event_category_detail->event_id, $event_category_detail->age_category_id, $event_category_detail->competition_category_id, $event_category_detail->distance_id, $club_member->club_id, $check_register_same_category);
            // }

            $check_success_category_mix = ArcheryEventParticipant::where('archery_event_participants.event_category_id', $event_category_detail->id)
                ->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                ->where('archery_event_participants.club_id', $club_member->club_id)
                ->where("archery_event_participants.status", 1)
                ->get()
                ->count();

            if ($check_success_category_mix > 3) {
                throw new BLoCException("club anda sudah terdaftar 3 kali pada kategori ini");
            }

            $check_panding_mix = ArcheryEventParticipant::select("archery_event_participants.*")->where('archery_event_participants.event_category_id', $event_category_detail->id)
                ->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                ->where('archery_event_participants.club_id', $club_member->club_id)
                ->where("archery_event_participants.status", 4)
                ->where("transaction_logs.expired_time", ">", $time_now)
                ->first();

            if ($check_panding_mix) {
                throw new BLoCException("terdapat pesanan yang belum di bayar oleh user dengan email " . $check_panding_mix->email);
            }

            $check_individu_category_detail_male = ArcheryEventCategoryDetail::where('event_id', $event_category_detail->event_id)
                ->where('age_category_id', $event_category_detail->age_category_id)
                ->where('competition_category_id', $event_category_detail->competition_category_id)
                ->where('distance_id', $event_category_detail->distance_id)
                ->where('team_category_id', "individu male")
                ->first();

            $check_individu_category_detail_female = ArcheryEventCategoryDetail::where('event_id', $event_category_detail->event_id)
                ->where('age_category_id', $event_category_detail->age_category_id)
                ->where('competition_category_id', $event_category_detail->competition_category_id)
                ->where('distance_id', $event_category_detail->distance_id)
                ->where('team_category_id', "individu female")
                ->first();

            if (!$check_individu_category_detail_male || !$check_individu_category_detail_female) {
                throw new BLoCException("kategori individu untuk kategori ini tidak tersedia");
            }

            $check_participant_male = ArcheryEventParticipant::join('archery_event_participant_members', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
                ->where("archery_event_participants.status", 1)
                ->where('archery_event_participants.event_category_id', $check_individu_category_detail_male->id)
                ->where('archery_event_participants.club_id', $club_member->club_id)
                ->count();

            $check_participant_female = ArcheryEventParticipant::join('archery_event_participant_members', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
                ->where("archery_event_participants.status", 1)
                ->where('archery_event_participants.event_category_id', $check_individu_category_detail_female->id)
                ->where('archery_event_participants.club_id', $club_member->club_id)
                ->count();

            if ($check_participant_male < (($check_success_category_mix + 1) * 1)) {
                throw new BLoCException("untuk pendaftaran ke " . $check_success_category_mix . " membutuhkan " . (($check_success_category_mix + 1) * 1) . " peserta laki-laki");
            }

            if ($check_participant_female < (($check_success_category_mix + 1) * 1)) {
                throw new BLoCException("untuk pendaftaran ke " . $check_success_category_mix . " membutuhkan " . (($check_success_category_mix + 1) * 1) . " peserta laki-laki");
            }
        } else {
            if ($check_register_same_category >= 3) {
                $check_panding = ArcheryEventParticipant::where('archery_event_participants.event_category_id', $event_category_detail->id)
                    ->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                    ->where('archery_event_participants.club_id', $club_member->club_id)
                    ->where("archery_event_participants.status", 4)
                    ->where("transaction_logs.expired_time", ">", $time_now)
                    ->count();
                if ($check_panding > 0)
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

            if (!$check_individu_category_detail) {
                throw new BLoCException("kategori individu untuk kategori ini tidak tersedia");
            }

            $check_participant = ArcheryEventParticipant::join('archery_event_participant_members', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
                ->where('archery_event_participants.event_category_id', $check_individu_category_detail->id)
                ->where('archery_event_participants.club_id', $club_member->club_id)
                ->count();
            if ($check_participant < (($check_register_same_category + 1) * 3)) {
                throw new BLoCException("untuk pendaftaran ke " . ($check_register_same_category + 1) . " minimal harus ada " . (($check_register_same_category + 1) * 3) . " peserta tedaftar dengan club ini");
            }
        }

        if ($participant) {
            $participant->status = 4;
            $participant->club_id = $club_member != null ? $club_member->club_id : 0;
            $participant->is_early_bird_payment = $with_early_bird;
            $participant->save();
        } else {
            // insert data participant
            // $participant = ArcheryEventParticipant::insertParticipant($user, Str::uuid(), $event_category_detail, 4, $club_member != null ? $club_member->club_id : 0, $is_marathon == 1 ? $day_choice : null, 0);
            $participant = ArcheryEventParticipant::insertParticipant($user, Str::uuid(), $event_category_detail, 4, $club_member->club_id, null, 0, $with_early_bird);
        }


        if ($price < 1) {
            $participant->status = 1;
            $participant->save();

            $res = [
                "archery_event_participant_id" => $participant->id,
                "payment_info" => null
            ];
            return $this->composeResponse($res);
        }

        $order_id = env("ORDER_ID_PREFIX", "OE-S") . $participant_new->id;
        $payment = PaymentGateWay::setTransactionDetail((int)$price, $order_id)
            ->setGateway($this->gateway)
            ->setCustomerDetails($user->name, $user->email, $user->phone_number)
            ->addItemDetail($event_category_detail->id, (int)$price, $event_category_detail->event_name)
            ->feePaymentsToUser($this->have_fee_payment_gateway)
            ->setMyarcheryFee($this->myarchery_fee)
            ->createSnap();
        $participant->transaction_log_id = $payment->transaction_log_id;
        $participant->save();
        $res = [
            "archery_event_participant_id" => $participant->id,
            "payment_info" => $payment
        ];
        return $this->composeResponse($res);
    }

    protected function validation($parameters)
    {
        return [
            "event_category_id" => "required",
            "club_id" => "required",
            "with_club" => "required"
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
