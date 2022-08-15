<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEvent;
use App\Models\TransactionLog;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Libraries\PaymentGateWay;
use App\Models\ArcheryClub;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class DetailEventOrder extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = Auth::guard('app-api')->user();
        $participant = ArcheryEventParticipant::find($parameters->get("id"));
        if (!$participant) {
            throw new BLoCException("participant tidak ditemukan");
        }

        if ($participant->user_id != $user->id) {
            throw new BLoCException("forbiden");
        }
        $archery_event = ArcheryEvent::find($participant->event_id);
        $transaction_info = PaymentGateWay::transactionLogPaymentInfo($participant->transaction_log_id);
        $participant_members = ArcheryEventParticipantMember::where("archery_event_participant_id", $participant->id)->get();
        $participant['club_detail'] = ArcheryClub::find($participant->club_id);
        $participant["members"] = $participant_members;
        $participant["category_label"] = $participant->team_category_id . "-" . $participant->age_category_id . "-" . $participant->competition_category_id . "-" . $participant->distance_id . "m";
        $participant["status_label"] = TransactionLog::getStatus($participant->status);

        $output = [
            "archery_event" => $archery_event,
            "participant" => $participant,
            "transaction_info" => $transaction_info,
        ];
        return $output;
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|exists:archery_event_participants,id',
        ];
    }
}
