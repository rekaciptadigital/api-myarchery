<?php

namespace App\BLoC\Web\EventOrder;

use AlterArcheryEventParticipant;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Abstracts\Transactional;
use App\Libraries\PaymentGateWay;
use App\Models\ArcheryEvent;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ClubMember;
use App\Models\ParticipantMemberTeam;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class AddEventOrder extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = Auth::guard('app-api')->user();
        $team_name = $parameters->get('team_name');
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
            throw new BLoCException('registration has ended');
        }

        // cek apakah user sudah tergabung dalam club atau belum
        $club_member = ClubMember::where('club_id', $parameters->get('club_id'))->where('user_id', $user->id)->first();
        if (!$club_member) {
            throw new BLoCException("member not joined this club");
        }

        if ($event_category_detail->category_team == ArcheryEventCategoryDetail::INDIVIDUAL_TYPE) {
            return $this->registerIndividu($event_category_detail, $user, $club_member, $team_name);
        } else {
            Validator::make($parameters->all(), [
                "user_id" => "required|array",
                "team_name" => "required|string"
            ])->validate();
            return $this->registerTeam($event_category_detail, $user, $club_member, $team_name, $user_id);
        }
    }

    private function registerIndividu($event_category_detail, $user, $club_member, $team_name)
    {
        $time_now = time();

        // hitung jumlah participant pada category yang didaftarkan user
        $participant_count = ArcheryEventParticipant::join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
            ->where("event_category_id", $event_category_detail->id)
            ->where(function ($query) use ($time_now) {
                $query->where("transaction_logs.status", 1);
                $query->orWhere(function ($q) use ($time_now) {
                    $q->where("transaction_logs.status", 4);
                    $q->where("transaction_logs.expired_time", ">", $time_now);
                });
            })->where('event_id', $event_category_detail->event_id)->get();

        if ($participant_count->count() >= $event_category_detail->quota) {
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

        // cek jika memiliki syarat umur
        if ($event_category_detail->max_age != 0) {
            if ($user->age == null) {
                throw new BLoCException("tgl lahir anda belum di set");
            }
            // cek apakah usia user memenuhi syarat categori event
            if ($user->age > $event_category_detail->max_age) {
                throw new BLoCException("tidak memenuhi syarat umur");
            }
        }

        $gender_category = $event_category_detail->gender_category;
        if ($user->gender != $gender_category) {
            throw new BLoCException('this category not for ' . $user->gender);
        }

        // cek apakah user telah pernah mendaftar di categori tersebut
        $isExist = ArcheryEventParticipant::where('event_category_id', $event_category_detail->id)
            ->where('user_id', $user->id)->get();
        if ($isExist->count() > 0) {
            throw new BLoCException("user already join this category event");
        }

        // insert data participant
        $participant = ArcheryEventParticipant::insertParticipant($user, Str::uuid(), $team_name, $event_category_detail, 4, $club_member->club_id);

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

            ParticipantMemberTeam::insertParticipantMemberTeam($participant, $member, $event_category_detail);

            $res = [
                "archery_event_participant_id" => $participant->id,
                "payment_info" => null
            ];
            return $this->composeResponse($res);
        }

        $payment = PaymentGateWay::setTransactionDetail((int)$event_category_detail->fee, $order_id)
            ->enabledPayments(["bca_va", "bni_va", "bri_va", "other_va", "gopay"])
            ->setCustomerDetails($user->name, $user->email, $user->phone_number)
            ->addItemDetail($event_category_detail->id, (int)$event_category_detail->fee, $event_category_detail->event_name)
            ->createSnap();

        app('redis')->set('participant_member_id', "individu");
        app('redis')->expire('participant_member_id', 86400);

        $participant->transaction_log_id = $payment->transaction_log_id;
        $participant->save();

        $res = [
            "archery_event_participant_id" => $participant->id,
            'payment_info' => $payment
        ];
        return $this->composeResponse($res);
    }

    private function registerTeam($event_category_detail, $user, $club_member, $team_name, $user_id)
    {
        // mengambil gender category
        $gender_category = $event_category_detail->gender_category;
        $participant_member_id = [];

        foreach ($user_id as $u) {
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

            // return $category;

            // cek apakah terdapat category individual
            if (!$category) {
                throw new BLoCException("category individual not found for this category");
            }


            $participant_old = ArcheryEventParticipant::where('event_category_id', $category->id)->where('user_id', $u)->first();
            if (!$participant_old) {
                if ($user->id == $u) {
                    throw new BLoCException("you are not joined individual category for this category");
                }
                throw new BLoCException("user with email " . $user_register->email . " not joined individual category for this category");
            }
            $participant_member_old = ArcheryEventParticipantMember::where('archery_event_participant_id', $participant_old->id)->first();
            if (!$participant_member_old) {
                throw new BLoCException("participant_member_old not found");
            }
            array_push($participant_member_id, $participant_member_old->id);
            $participant_member_team = ParticipantMemberTeam::where('participant_member_id', $participant_member_old->id)->where('event_category_id', $event_category_detail->id)->first();
            // return $participant_member_team;
            if ($participant_member_team) {
                throw new BLoCException("user with id " . $u . " already join this category");
            }
        }

        $participant_new = ArcheryEventParticipant::insertParticipant($user, Str::uuid(), $team_name, $event_category_detail, 4, $club_member->club_id);

        if ($event_category_detail->fee < 1) {
            $participant_new->status = 1;
            $participant_new->save();

            foreach ($user_id as $u) {
                $participant_old = ArcheryEventParticipant::where('event_category_id', $category->id)->where('user_id', $u)->first();
                $participant_member_old = ArcheryEventParticipantMember::where('user_id', $u)->where('archery_event_participant_id', $participant_old->id)->first();
                if ($participant_member_old) {
                    ParticipantMemberTeam::insertParticipantMemberTeam($participant_old, $participant_member_old, $event_category_detail);
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
            ->enabledPayments(["bca_va", "bni_va", "bri_va", "other_va", "gopay"])
            ->setCustomerDetails($user->name, $user->email, $user->phone_number)
            ->addItemDetail($event_category_detail->id, (int)$event_category_detail->fee, $event_category_detail->event_name)
            ->createSnap();

        // $participant_member_id = [];
        // foreach ($user_id as $u) {
        //     $participant_old = ArcheryEventParticipant::where('event_category_id', $category->id)->where('user_id', $u)->first();
        //     if (!$participant_old) {
        //         throw new BLoCException("user with id " . $u . " not joined individual category");
        //     }
        //     $participant_member_old = ArcheryEventParticipantMember::where('archery_event_participant_id', $participant_old->id)->first();
        //     if (!$participant_member_old) {
        //         throw new BLoCException("user with id " . $u . " not joined individual category");
        //     }
        //     if ($participant_member_old) {
        //         array_push($participant_member_id, $participant_member_old->id);
        //     }
        // }
        $out_json = json_encode($participant_member_id);
        app('redis')->set('participant_member_id', $out_json);
        app('redis')->expire('participant_member_id', 86400);

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
