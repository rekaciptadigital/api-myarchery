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

        $partticipant = ArcheryEventParticipant::find($participant_id);
        if (!$partticipant) {
            throw new BLoCException("data participant tidak tersedia");
        }

        $category_participant = ArcheryEventCategoryDetail::find($partticipant->event_category_id);
        if (!$category_participant) {
            throw new BLoCException("kategori participant tidak ditemukan");
        }

        $user = User::find($partticipant->user_id);
        if (!$user) {
            throw new BLoCException("user tidak ditemukan");
        }

        // cek category tujuan apakah tersedia atau tidak
        $category = ArcheryEventCategoryDetail::find($category_id);
        if (!$category) {
            throw new BLoCException("Kategori tidak ditemukan");
        }

        $event = ArcheryEvent::find($category->event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        if ($partticipant->event_id != $event->id) {
            throw new BLoCException("event tidak sama");
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException("forbiden");
        }

        if ($partticipant->status != 1) {
            throw new BLoCException("tidak dapat mengganti kategori");
        }

        $isExist = ArcheryEventParticipant::where('event_category_id', $category->id)
            ->where('user_id', $user->id)
            ->get();

        if ($isExist->count() > 0) {
            foreach ($isExist as $ie) {
                if ($ie->status == 1) {
                    // throw new BLoCException("event dengan kategori ini sudah di ikuti");
                }

                if ($ie->status == 4) {
                    $ie_transaction_log = TransactionLog::find($ie->transaction_log_id);
                    if ($ie_transaction_log) {
                        if ($ie_transaction_log->status == 4 && $ie_transaction_log->expired_time > time()) {
                            // throw new BLoCException("transaksi dengan kategory ini sudah pernah dilakukan, silahkan selesaikan pembayaran atau batalkan pesanan");
                        }
                    }
                }
            }
        }

        if (($category->category_team == ArcheryEventCategoryDetail::INDIVIDUAL_TYPE) && ($category_participant->category_team == ArcheryEventCategoryDetail::INDIVIDUAL_TYPE)) {
            return $this->changeCategoryIndividu($category, $user, $partticipant, $event, $category_participant);
        } elseif (($category->category_team == ArcheryEventCategoryDetail::TEAM_TYPE) && ($category_participant->category_team == ArcheryEventCategoryDetail::TEAM_TYPE)) {
            return $this->changeCategoryTeam($category, $partticipant);
        } else {
            throw new BLoCException("tipe kategori tidak sama");
        }
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

    private function changeCategoryIndividu($new_category, $user_participant, $partticipant, $event, $category_participant)
    {

        $participant_memmber = ArcheryEventParticipantMember::where('archery_event_participant_id', $partticipant->id)->first();
        if (!$participant_memmber) {
            throw new BLoCException("participant member tidak tersedia");
        }

        if ($new_category->max_age != 0) {
            if ($user_participant->age == null) {
                throw new BLoCException("tgl lahir user belum di set");
            }
            $check_date = $this->getAge($user_participant->date_of_birth, $event->event_start_datetime);
            // cek apakah usia user memenuhi syarat categori event
            if ($check_date["y"] > $new_category->max_age) {
                // throw new BLoCException("tidak memenuhi syarat usia, syarat maksimal usia adalah " . $new_category->max_age . " tahun");
            }
            if ($check_date["y"] == $new_category->max_age && ($check_date["m"] > 0 || $check_date["d"] > 0)) {
                // throw new BLoCException("tidak memenuhi syarat usia, syarat maksimal usia adalah " . $new_category->max_age . " tahun");
            }
        }

        // cek jika memiliki syarat minimal umur
        if ($new_category->min_age != 0) {
            if ($user_participant->age == null) {
                throw new BLoCException("tgl lahir user belum di set");
            }
            // cek apakah usia user memenuhi syarat categori event
            if ($user_participant->age < $new_category->min_age) {
                // throw new BLoCException("tidak memenuhi syarat usia, minimal usia adalah " . $new_category->min_age . " tahun");
            }
        }

        $gender_category = $new_category->gender_category;
        if ($user_participant->gender != $gender_category) {
            if (empty($user_participant->gender))
                throw new BLoCException('user belum mengatur gender');

            throw new BLoCException('oops.. kategori ini  hanya untuk gender ' . $gender_category);
        }

        $now = Carbon::now();
        $new_format = Carbon::parse($new_category->start_event);

        if ($now > $new_format) {
            // throw new BLoCException("event telah lewat");
        }

        if ($new_format->diffInDays($now) < 1) {
            // throw new BLoCException("tidak dapat mengubah kategori, minimal mengubah kategori adalah 24 jam sebelum berlangsungnya event");
        }

        $participant_count = ArcheryEventParticipant::countEventUserBooking($new_category->id);
        if ($participant_count >= $new_category->quota) {
            $msg = "quota untuk kategori ini sudah penuh";
            // check kalo ada pembayaran yang pending
            $participant_count_pending = ArcheryEventParticipant::join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                ->where("archery_event_participants.event_category_id", $new_category->id)
                ->where("transaction_logs.status", 4)->where("transaction_logs.expired_time", ">", time())
                ->count();

            if ($participant_count_pending > 0) {
                $msg = "untuk sementara  " . $msg . ", silahkan coba beberapa saat lagi";
            } else {
                $msg = $msg . ", silahkan daftar atau pindah di kategori lain";
            }
            // throw new BLoCException($msg);
        }

        $partticipant->update([
            "event_category_id" => $new_category->id,
            "team_category_id" => $new_category->team_category_id,
            "age_category_id" => $new_category->age_category_id,
            "competition_category_id" => $new_category->competition_category_id,
            "distance_id" => $new_category->distance_id
        ]);

        $participant_member_team_old = ParticipantMemberTeam::where('participant_id', $partticipant->id)
            ->where('event_category_id', $category_participant->id)->first();
        if (!$participant_member_team_old) {
            throw new BLoCException("participant member team tidak ditemukan");
        }

        $participant_member_team_old->update([
            "event_category_id" => $new_category->id,
        ]);

        $qualification_time = ArcheryEventQualificationTime::where("category_detail_id", $new_category->id)->first();
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

    private function changeCategoryTeam($new_category, $partticipant)
    {
        $gender_category = $new_category->gender_category;
        $time_now = time();
        // cek total pendaftar yang masih pending dan sukses
        $check_register_same_category = ArcheryEventParticipant::where('archery_event_participants.event_category_id', $new_category->id)
            ->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
            ->where('archery_event_participants.club_id', $partticipant->club_id)
            ->where(function ($query) use ($time_now) {
                $query->where("archery_event_participants.status", 1);
                $query->orWhere(function ($q) use ($time_now) {
                    $q->where("archery_event_participants.status", 4);
                    $q->where("transaction_logs.expired_time", ">", $time_now);
                });
            })
            ->count();

        if ($gender_category == 'mix') {
            if ($check_register_same_category >= 3) {
                $check_panding = ArcheryEventParticipant::where('archery_event_participants.event_category_id', $new_category->id)
                    ->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                    ->where('archery_event_participants.club_id', $partticipant->club_id)
                    ->where("archery_event_participants.status", 4)
                    ->where("transaction_logs.expired_time", ">", $time_now)
                    ->count();
                if ($check_panding > 0)
                    throw new BLoCException("ada transaksi yang belum diselesaikan oleh club pada category ini");
                else
                    throw new BLoCException("club anda sudah terdaftar 2 kali di kategory ini");
            }
        } else {
            if ($check_register_same_category >= 3) {
                $check_panding = ArcheryEventParticipant::where('archery_event_participants.event_category_id', $new_category->id)
                    ->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                    ->where('archery_event_participants.club_id', $partticipant->club_id)
                    ->where("archery_event_participants.status", 4)
                    ->where("transaction_logs.expired_time", ">", $time_now)
                    ->count();
                if ($check_panding > 0)
                    throw new BLoCException("ada transaksi yang belum diselesaikan oleh club pada category ini");
                else
                    throw new BLoCException("club anda sudah terdaftar 2 kali di kategory ini");
            }
            $team_category_id = $new_category->team_category_id == "female_team" ? "individu female" : "individu male";
            $check_individu_category_detail = ArcheryEventCategoryDetail::where('event_id', $new_category->event_id)
                ->where('age_category_id', $new_category->age_category_id)
                ->where('competition_category_id', $new_category->competition_category_id)
                ->where('distance_id', $new_category->distance_id)
                ->where('team_category_id', $team_category_id)
                ->first();

            if (!$check_individu_category_detail) {
                throw new BLoCException("kategori individu untuk kategori ini tidak tersedia");
            }

            $check_participant = ArcheryEventParticipant::join('archery_event_participant_members', 'archery_event_participants.id', '=', 'archery_event_participant_members.archery_event_participant_id')
                ->where('archery_event_participants.event_category_id', $check_individu_category_detail->id)
                ->where('archery_event_participants.club_id', $partticipant->club_id)
                ->count();
            if ($check_participant < (($check_register_same_category + 1) * 3)) {
                throw new BLoCException("untuk pendaftaran ke " . ($check_register_same_category + 1) . " minimal harus ada " . (($check_register_same_category + 1) * 3) . " peserta tedaftar dengan club ini");
            }
        }

        $partticipant->update([
            "event_category_id" => $new_category->id,
            "team_category_id" => $new_category->team_category_id,
            "age_category_id" => $new_category->age_category_id,
            "competition_category_id" => $new_category->competition_category_id,
            "distance_id" => $new_category->distance_id
        ]);

        return $partticipant;
    }
}
