<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEventParticipant;
use App\Models\TransactionLog;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;

class ChangeStatusTransaction extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $participant_id = $parameters->get("participant_id");
        $status = $parameters->get("status");
        $participant = ArcheryEventParticipant::find($participant_id);

        if ($participant->status == $status) {
            throw new BLoCException("transaction already changes");
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
    }

    protected function validation($parameters)
    {
        return [
            'participant_id' => 'required|exists:archery_event_participants,id',
            "status" => "required|in:1,2,5"
        ];
    }
}
