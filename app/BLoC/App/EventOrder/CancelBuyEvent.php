<?php

namespace App\BLoC\App\EventOrder;

use App\Models\TransactionLog;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;
use App\Models\ArcheryEventParticipant;
use DAI\Utils\Exceptions\BLoCException;

class CancelBuyEvent extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user_login = Auth::guard('app-api')->user();
        $participant_id = $parameters->get("participant_id");

        $participant = ArcheryEventParticipant::select(
            "archery_event_participants.*",
            "transaction_logs.id as transaction_id",
            "transaction_logs.status as transaction_status",
            "transaction_logs.expired_time as transaction_expired_time"
        )
            ->leftJoin(
                "transaction_logs",
                "transaction_logs.id",
                "=",
                "archery_event_participants.transaction_log_id"
            )->where("archery_event_participants.id", $participant_id)
            ->first();

        if (
            $participant->status == 1 ||
            $participant->status == 4 &&
            $participant->transaction_status == 4 &&
            $participant->transaction_expired_time > time()
        ) {
            $participant->status = 3;
            $participant->save();

            $transaction_log = TransactionLog::find($participant->transaction_id);
            if ($transaction_log) {
                $transaction_log->status = 3;
                $transaction_log->save();
            }
        } else {
            throw new BLoCException("invalid status to cancel");
        }

        return "success cancel";
    }

    protected function validation($parameters)
    {
        return [
            "participant_id" => "required|exists:archery_event_participants,id"
        ];
    }
}
