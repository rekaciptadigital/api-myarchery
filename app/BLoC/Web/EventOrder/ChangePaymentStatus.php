<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\TransactionLog;
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

        // jika status == 1 periksa quota terlebih dahulu
        if ($status == 1) {
            $time_now = time();
            // dapatkan categori participant
            $category = ArcheryEventCategoryDetail::find($participant->event_category_id);
            // cek kuota kategori tujuan
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
        }

        if ($participant->transaction_log_id != 0) {
            $transactionLog = TransactionLog::find($participant->transaction_log_id);
            if (!$transactionLog) {
                throw new BLoCException("transaction log not found");
            }

            $transactionLog->status = $status;
            $transactionLog->save();
        }

        $participant->status;
        $participant->save();
    }

    protected function validation($parameters)
    {
        return [
            'participant_id' => 'required|exists:archery_event_participants,id',
            "status" => "required|in:1,2,5"
        ];
    }
}
