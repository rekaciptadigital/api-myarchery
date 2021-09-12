<?php

namespace App\BLoC\Web\EventOrder;

use App\Libraries\PaymentGateWay;
use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use Illuminate\Support\Facades\Auth;

class GetEventOrder extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = Auth::user();
        $output = array();
        $participants = ArcheryEventParticipant::where("user_id",$user["id"])->get();
        foreach ($participants as $key => $participant) {
            $archery_event = ArcheryEvent::find($participant->event_id);
            $transaction_info = PaymentGateWay::transactionLogPaymentInfo($participant->transaction_log_id);
            $participant_members = ArcheryEventParticipantMember::where("archery_event_participant_id", $participant->id)->get();
            $participant["members"] = $participant_members;
            $output[] = [
                "archery_event" => $archery_event,
                "participant" => $participant,
                "transaction_info" => $transaction_info,
            ];
        }

        return $output;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
