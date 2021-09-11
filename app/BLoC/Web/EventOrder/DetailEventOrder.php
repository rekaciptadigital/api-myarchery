<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Transactional;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Libraries\PaymentGateWay;
class DetailEventOrder extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $participant = ArcheryEventParticipant::find($parameters->get("id"));
        $archery_event = ArcheryEvent::find($participant->event_id);
        $transaction_info = PaymentGateWay::TransactionLogPaymentInfo($participant->transaction_log_id);
        $participant_members = ArcheryEventParticipantMember::where("archery_event_participant_id",$participant->id)->get();
        $participant["members"] = $participant_members;
        $output = [
            "archery_event" => $archery_event,
            "participant" => $participant,
            "transaction_info" => $transaction_info,
        ];
        return $output;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
