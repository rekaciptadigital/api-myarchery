<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipantMemberNumber;
use App\Models\ArcheryEventParticipantNumber;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\ArcheryEventQualificationTime;
use App\Models\ArcheryMasterTeamCategory;
use App\Models\ArcherySeriesUserPoint;
use App\Models\TransactionLog;
use App\Models\User;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;

class ChangePaymentStatus extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        // tangkap param
        $participant_id = $parameters->get("participant_id");
        $status = $parameters->get("status");


        // dapatkan participant
        $participant = ArcheryEventParticipant::find($participant_id);

        // jika status saat ini == status yang ingin diubah maka lemparkan error
        if ($participant->status == $status) {
            throw new BLoCException("transaction already changes");
        }

        // dapatkan categori participant
        $category = ArcheryEventCategoryDetail::find($participant->event_category_id);
        $team_category = ArcheryMasterTeamCategory::find($category->team_category_id);
        $user_participant = User::find($participant->user_id);

        // update status participant
        $participant->status;
        $participant->save();

        // jika ada transaction log maka update transaction log
        if ($participant->transaction_log_id != 0) {
            $transactionLog = TransactionLog::find($participant->transaction_log_id);
            if (!$transactionLog) {
                throw new BLoCException("transaction log not found");
            }

            $transactionLog->status = $status;
            $transactionLog->save();
        }

        if ($status == 1) {
            return $this->changeToSuccessStatus($category, $team_category, $participant, $user_participant);
        }

        if ($status == 5 || $status == 2) {
            return $this->changeToRefundOrFailedStatus($participant, $category);
        }
    }

    protected function validation($parameters)
    {
        return [
            'participant_id' => 'required|exists:archery_event_participants,id',
            "status" => "required|in:1,2,5"
        ];
    }

    private function changeToSuccessStatus(ArcheryEventCategoryDetail $category, ArcheryMasterTeamCategory $team_category, ArcheryEventParticipant $participant, User $user_participant)
    {
        $time_now = time();
        // cek kuota 
        $participant_count = ArcheryEventParticipant::countEventUserBooking($category->id); // hitung jumlah peserta yang status transaksi nya sukses, pending, dan booking 
        $quota_left = $category->quota - $participant_count;
        if ($quota_left < 1) {
            $msg = "quota kategori ini sudah penuh";
            // check kalo ada pembayaran yang pending
            $participant_count_pending = ArcheryEventParticipant::join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                ->where("event_category_id", $category->id)
                ->where("archery_event_participants.status", 4)
                ->where("transaction_logs.status", 4)
                ->where("transaction_logs.expired_time", ">", $time_now)
                ->where("event_id", $category->event_id)
                ->count();

            if ($participant_count_pending > 0) {
                $msg = "untuk sementara  " . $msg . ", silahkan coba beberapa saat lagi";
            } else {
                $msg = $msg . ", silahkan daftar di kategori lain";
            }
            throw new BLoCException($msg);
        }

        if ($team_category->type == "Individual") {
            $qualification_time = ArcheryEventQualificationTime::where("category_detail_id", $category->id)
                ->first();
            if ($qualification_time) {
                throw new BLoCException("qualification time not found");
            }

            // cek member jika tidak ada maka create member baru
            $member = ArcheryEventParticipantMember::where("archery_event_participant_id", $participant->id)
                ->first();
            if (!$member) {
                // insert ke archery_event_participant_member
                $member = ArcheryEventParticipantMember::create([
                    "archery_event_participant_id" => $participant->id,
                    "name" => $user_participant->name,
                    "gender" => $user_participant->gender,
                    "birthdate" => $user_participant->date_of_birth,
                    "age" => $user_participant->age,
                    "team_category_id" => $category->team_category_id,
                    "user_id" => $user_participant->id
                ]);
            }

            $schedule = ArcheryEventQualificationScheduleFullDay::where("participant_member_id", $member->id)
                ->first();
            if (!$schedule) {
                ArcheryEventQualificationScheduleFullDay::create([
                    'qalification_time_id' => $qualification_time->id,
                    'participant_member_id' => $member->id,
                ]);
            }


            ArcherySeriesUserPoint::setAutoUserMemberCategory($category->event_id, $participant->user_id);
        }

        return "success";
    }

    private function changeToRefundOrFailedStatus(ArcheryEventParticipant $participant)
    {
        $participant_memmber = ArcheryEventParticipantMember::where("archery_event_participant_id", $participant->id)->first();
        if (!$participant_memmber) {
            throw new BLoCException("data participant member tidak tersedia");
        }

        $qualification_full_day = ArcheryEventQualificationScheduleFullDay::where("participant_member_id", $participant_memmber->id)->first();
        if (!$qualification_full_day) {
            throw new BLoCException("data qualification full day tidak ditemukan");
        }
        $qualification_full_day->delete();

        $member_number_prefix =  ArcheryEventParticipantMemberNumber::where("user_id", $participant_memmber->user_id)
            ->where("event_id", $participant->event_id)->first();
        if ($member_number_prefix) {
            $member_number_prefix->delete();
        }

        $participant_number = ArcheryEventParticipantNumber::where("participant_id", $participant->id)->first();
        if ($participant_number) {
            $participant_number->delete();
        }

        $participant_memmber->delete();

        return "success";
    }
}
