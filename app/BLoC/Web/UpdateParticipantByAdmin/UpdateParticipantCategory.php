<?php

namespace App\BLoC\Web\UpdateParticipantByAdmin;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\ArcheryEventQualificationTime;
use App\Models\ParticipantMemberTeam;
use App\Models\TransactionLog;
use App\Models\User;
use DateTime;
use Illuminate\Support\Carbon;

class UpdateParticipantCategory extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $participant_id = $parameters->get('participant_id');
        $category_id = $parameters->get('category_id');
        $time_now = time();

        $partticipant = ArcheryEventParticipant::find($participant_id);
        if (!$partticipant) {
            throw new BLoCException("data participant tidak tersedia");
        }

        $participant_memmber = ArcheryEventParticipantMember::where('archery_event_participant_id', $partticipant->id)->first();
        if (!$participant_memmber) {
            throw new BLoCException("participant member tidak tersedia");
        }

        $user = User::find($partticipant->user_id);
        if (!$user) {
            throw new BLoCException("user tidak ditemukan");
        }

        $category = ArcheryEventCategoryDetail::find($category_id);
        if (!$category) {
            throw new BLoCException("Kategori tidak ditemukan");
        }

        $event = ArcheryEvent::find($category->event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException("forbiden");
        }

        if ($partticipant->status != 1) {
            throw new BLoCException("tidak dapat mengganti kategori");
        }

        $category_participant = ArcheryEventCategoryDetail::find($partticipant->event_category_id);
        if (!$category_participant) {
            throw new BLoCException("kategori participant tidak ditemukan");
        }
        $category_detai_team = ArcheryEventCategoryDetail::where('event_id', $category_participant->event_id)
            ->where('age_category_id', $category_participant->age_category_id)
            ->where('competition_category_id', $category_participant->competition_category_id)
            ->where('distance_id', $category_participant->distance_id)
            ->where(function ($query) use ($user) {
                return $query->where('team_category_id', $user->gender . "_team")->orWhere('team_category_id', 'mix_team');
            })->get();

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

        $isExist = ArcheryEventParticipant::where('event_category_id', $category->id)
            ->where('user_id', $user->id)
            ->get();

        if ($isExist->count() > 0) {
            foreach ($isExist as $ie) {
                if ($ie->status == 1) {
                    throw new BLoCException("event dengan kategori ini sudah di ikuti");
                }
                $ie_transaction_log = TransactionLog::find($ie->transaction_log_id);
                if ($ie_transaction_log) {
                    if ($ie_transaction_log->status == 4 && $ie_transaction_log->expired_time > time()) {
                        throw new BLoCException("transaksi dengan kategory ini sudah pernah dilakukan, silahkan selesaikan pembayaran atau batalkan pesanan");
                    }
                }
            }
        }

        if ($category->max_age != 0) {
            if ($user->age == null) {
                throw new BLoCException("tgl user belum di set");
            }
            $check_date = $this->getAge($user->date_of_birth, $event->event_start_datetime);
            // cek apakah usia user memenuhi syarat categori event
            if ($check_date["y"] > $category->max_age) {
                throw new BLoCException("tidak memenuhi syarat usia, syarat maksimal usia adalah " . $category->max_age . " tahun");
            }
            if ($check_date["y"] == $category->max_age && ($check_date["m"] > 0 || $check_date["d"] > 0)) {
                throw new BLoCException("tidak memenuhi syarat usia, syarat maksimal usia adalah " . $category->max_age . " tahun");
            }
        }

        // cek jika memiliki syarat minimal umur
        if ($category->min_age != 0) {
            if ($user->age == null) {
                throw new BLoCException("tgl lahir user belum di set");
            }
            // cek apakah usia user memenuhi syarat categori event
            if ($user->age < $category->min_age) {
                throw new BLoCException("tidak memenuhi syarat usia, minimal usia adalah " . $category->min_age . " tahun");
            }
        }

        $gender_category = $category->gender_category;
        if ($user->gender != $gender_category) {
            if (empty($user->gender))
                throw new BLoCException('user belum mengatur gender');

            throw new BLoCException('oops.. kategori ini  hanya untuk gender ' . $gender_category);
        }

        $now = Carbon::now();
        $new_format = Carbon::parse($category->start_event);

        if ($new_format->diffInDays($now) < 1) {
            throw new BLoCException("tidak dapat mengubah kategori, minimal mengubah kategori adalah 1 hari sebelum berlangsungnya event");
        }

        $participant_count = ArcheryEventParticipant::countEventUserBooking($category->id);
        if ($participant_count >= $category->quota) {
            $msg = "quota untuk kategori ini sudah penuh";
            // check kalo ada pembayaran yang pending
            $participant_count_pending = ArcheryEventParticipant::join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                ->where("archery_event_participants.event_category_id", $category->id)
                ->where("transaction_logs.status", 4)->where("transaction_logs.expired_time", ">", $time_now)
                ->count();

            if ($participant_count_pending > 0) {
                $msg = "untuk sementara  " . $msg . ", silahkan coba beberapa saat lagi";
            } else {
                $msg = $msg . ", silahkan daftar atau pindah di kategori lain";
            }
            throw new BLoCException($msg);
        }

        $partticipant->update([
            "event_category_id" => $category->id
        ]);

        $participant_member_team_old = ParticipantMemberTeam::where('participant_id', $partticipant->id)
            ->where('event_category_id', $category_participant->id)->first();
        if (!$participant_member_team_old) {
            throw new BLoCException("participant member team tidak ditemukan");
        }

        $participant_member_team_old->update([
            "event_category_id" => $category->id,
        ]);

        $qualification_time = ArcheryEventQualificationTime::where("category_detail_id", $category->id)->first();
        if (!$qualification_time) {
            throw new BLoCException("waktu kualifikasi belum di set untuk kategory ini");
        }

        $qualification_full_day = ArcheryEventQualificationScheduleFullDay::where("participant_member_id", $participant_memmber->id)->first();
        if (!$qualification_full_day) {
            throw new BLoCException("jadwal kualifikasi untuk user ini belum diatur");
        }
        $qualification_full_day->update([
            "qalification_time_id" => $qualification_time->id
        ]);

        return [
            "participant" => $partticipant,
            "participant_member" => $participant_memmber,
            "participant_member_team" => $participant_member_team_old,
            "qualification_time" => $qualification_time,
            "qualification_full_day" => $qualification_full_day
        ];
    }

    protected function validation($parameters)
    {
        return [
            'participant_id' => 'required|integer',
            'category_id' => 'required|integer'
        ];
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
}
