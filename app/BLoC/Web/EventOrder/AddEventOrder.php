<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Abstracts\Transactional;
use App\Libraries\PaymentGateWay;
use Illuminate\Support\Facades\Auth;

class AddEventOrder extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = Auth::user();
        $event = ArcheryEvent::find($parameters->event_id);
        $total_price = 0;
        // TODO START ADD LOGIC FOR PRICE
        $total_price =10000; // sampel data
        if(!$event->is_flat_registration_fee){

        }
        // TODO END ADD LOGIC FOR PRICE

        $participant = new ArcheryEventParticipant;
        $participant->event_id = $event->id;
        $participant->user_id = $user["id"];
        $participant->name = "-";
        $participant->club = $parameters->club_name;
        $participant->email = $parameters->email;
        $participant->type = $parameters->type;
        $participant->phone_number = $parameters->phone_number;
        $participant->competition_category = $parameters->category_event;
        $participant->team_name = $parameters->team_name;
        $participant->team_category = "-";
        $participant->age_category = "-";
        $participant->distance = 0;
        $participant->transaction_log_id = 0;
        $participant->save();
        
        $member = array();
        $order_id = env("ORDER_ID_PREFIX","OE-S").$participant->id;
        foreach ($parameters->participant_members as $key => $value) {
            $birth_date = explode("-", $value["birthdate"]);
            //get age from date or birthdate
            $age = (date("md", date("U", mktime(0, 0, 0, $birth_date[2], $birth_date[1], $birth_date[0]))) > date("md")
                ? ((date("Y") - $birth_date[0]) - 1)
                : (date("Y") - $birth_date[0]));

            $member[] = [
                "archery_event_participant_id" => $participant->id,
                "name" => $value["name"],
                "gender" => $value["gender"],
                "birthdate" => $value["birthdate"],
                "age" => $age,
                "team_category" => "-"
            ];
        }
        ArcheryEventParticipantMember::insert($member);
        
        $payment = PaymentGateWay::setTransactionDetail($total_price,$order_id)
                                    ->enabledPayments(["bca_va", "bni_va", "bri_va", "other_va", "gopay"])
                                    ->setCustomerDetails($user["name"],$user["email"],$user["phone_number"])
                                    ->addItemDetail($event->id,$total_price,$event->event_name)
                                    ->CreateSnap();

        $participant->transaction_log_id = $payment->transaction_log_id;
        $participant->save();
       
        return ["archery_event_participant_id" => $participant->id];
    }

    protected function validation($parameters)
    {
        return [
            "type" => "in:team,individual",
            "category_event" => "required",
            "event_id" => "exists:archery_events,id"
        ];
    }
}
