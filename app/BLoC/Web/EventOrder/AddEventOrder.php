<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Abstracts\Transactional;
use App\Libraries\PaymentGateWay;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AddEventOrder extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = Auth::guard('app-api')->user();
        $event = ArcheryEvent::find($parameters->event_id);
        $total_price = 0;

        $event_category = $parameters->get('category_event');

        $total_price = BLoC::call('getEventPrice', $parameters->all());

        if ($total_price == 0) {
            throw new BLoCException("Price not found");
        }

        $participant = new ArcheryEventParticipant;
        $participant->event_id = $event->id;
        $participant->user_id = $user["id"];
        $participant->name = $parameters->team_name;
        $participant->club = $parameters->club_name;
        $participant->email = $parameters->email;
        $participant->type = $parameters->type;
        $participant->phone_number = $parameters->phone_number;
        $participant->team_name = $parameters->team_name;
        $participant->competition_category_id = $event_category['competition_category_id'];
        $participant->team_category_id = $event_category['team_category_id'];
        $participant->age_category_id = $event_category['age_category_id'];
        $participant->distance_id = $event_category['distance_id'];
        $participant->transaction_log_id = 0;
        $participant->unique_id = Str::uuid();
        $participant->save();

        $member = array();
        $order_id = env("ORDER_ID_PREFIX", "OE-S") . $participant->id;
        foreach ($parameters->participant_members as $key => $value) {
            $age = null;
            if (!is_null($value["birthdate"]) && $value["birthdate"] != '') {
                $birth_date = explode("-", $value["birthdate"]);
                //get age from date or birthdate
                $age = (date("md", date("U", mktime(0, 0, 0, $birth_date[2], $birth_date[1], $birth_date[0]))) > date("md")
                    ? ((date("Y") - $birth_date[0]) - 1)
                    : (date("Y") - $birth_date[0]));
            }

            $member[] = [
                "archery_event_participant_id" => $participant->id,
                "name" => $value["name"],
                "gender" => $value["gender"] != '' ? $value["gender"] : null,
                "birthdate" => $value["birthdate"] == '' ? null : $value["birthdate"],
                "age" => $age,
                "team_category_id" => $event_category['team_category_id']
            ];
        }
        ArcheryEventParticipantMember::insert($member);

        $payment = PaymentGateWay::setTransactionDetail($total_price, $order_id)
            ->enabledPayments(["bca_va", "bni_va", "bri_va", "other_va", "gopay"])
            ->setCustomerDetails($user["name"], $user["email"], $user["phone_number"])
            ->addItemDetail($event->id, $total_price, $event->event_name)
            ->createSnap();

        $participant->transaction_log_id = $payment->transaction_log_id;
        $participant->save();

        return ["archery_event_participant_id" => $participant->id];
    }

    protected function validation($parameters)
    {
        return [
            "type" => "in:team,individual",
            "category_event" => "required",
            "event_id" => "required|exists:archery_events,id"
        ];
    }
}
