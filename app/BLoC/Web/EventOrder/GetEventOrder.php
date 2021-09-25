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
        $user = Auth::guard('app-api')->user();
        $output = array();
        $participants = ArcheryEventParticipant::where("user_id",$user["id"])->orderBy("id","DESC")->get();
        foreach ($participants as $key => $participant) {
            $archery_event = ArcheryEvent::find($participant->event_id);
            $transaction_info = PaymentGateWay::transactionLogPaymentInfo($participant->transaction_log_id);
            $participant_members = ArcheryEventParticipantMember::where("archery_event_participant_id", $participant->id)->get();
            $participant["members"] = $participant_members;
            
            $flat_categorie = $archery_event->flatCategories;
            $category_label = $participant->team_category_id."-".$participant->age_category_id."-".$participant->competition_category_id."-".$participant->distance_id."m";
            foreach ($flat_categorie as $key => $value) {
                if($value->age_category_id == $participant->age_category_id
                && $value->competition_category_id == $participant->competition_category_id
                && $value->team_category_id == $participant->team_category_id
                && $value->distance_id == $participant->distance_id
                ){
                    $category_label = $value->archery_event_category_label;
                }
            }
            $participant->category_label = $category_label;
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
