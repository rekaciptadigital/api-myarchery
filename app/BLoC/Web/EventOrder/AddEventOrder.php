<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Abstracts\Transactional;
use App\Libraries\PaymentGateWay;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        $event_category = $parameters->get('category_event');

        $archery_event_price_query = "
            SELECT A.*, C.*, B.price as flat_price,
                B.start_date as early_bird_start_date,
                B.end_date as early_bird_end_date,
                B.registration_type
            FROM archery_events A
            JOIN archery_event_registration_fees B ON A.id = B.event_id
            JOIN archery_event_registration_fees_per_category C ON B.id = C.event_registration_fee_id
            WHERE A.id = :event_id
            AND C.team_category = :team_category
        ";
        $archery_event_price_results = DB::SELECT($archery_event_price_query, [
            "event_id" => $parameters->event_id,
            "team_category" => $event_category['team_category_id']
        ]);

        $archery_event_price_normal = collect($archery_event_price_results)->firstWhere('registration_type', '=', 'normal');
        $archery_event_price_early_bird = collect($archery_event_price_results)->firstWhere('registration_type', '=', 'early_bird');

        if (is_null($archery_event_price_normal) && is_null($archery_event_price_early_bird)) {
            throw new BLoCException("Price Not Found");
        }

        $normal_price = $archery_event_price_normal->price;
        $normal_flat_price = $archery_event_price_normal->flat_price;

        $total_price = $normal_price;

        if ($archery_event_price_normal->is_flat_registration_fee) {
            $total_price = $normal_flat_price;
        }

        if (!is_null($archery_event_price_early_bird)) {
            $date_now = date("Y-m-d");
            $early_bird_start_date = $archery_event_price_early_bird->early_bird_start_date;
            $early_bird_end_date = $archery_event_price_early_bird->early_bird_end_date;
            if ($early_bird_start_date <= $date_now && $date_now <= $early_bird_end_date) {
                $total_price = $archery_event_price_early_bird->price;

                if ($archery_event_price_normal->is_flat_registration_fee) {
                    $total_price = $archery_event_price_early_bird->flat_price;
                }
            }
        }

        $participant = new ArcheryEventParticipant;
        $participant->event_id = $event->id;
        $participant->user_id = $user["id"];
        $participant->name = $parameters->team_name;
        $participant->club = $parameters->club_name;
        $participant->email = $parameters->email;
        $participant->type = $parameters->type;
        $participant->phone_number = $parameters->phone_number;
        $participant->competition_category = $event_category['competition_category_id'];
        $participant->team_name = $parameters->team_name;
        $participant->team_category = $event_category['team_category_id'];
        $participant->age_category = $event_category['age_category_id'];
        $participant->distance = $event_category['distance_id'];
        $participant->transaction_log_id = 0;
        $participant->unique_id = Str::uuid();
        $participant->save();

        $member = array();
        $order_id = env("ORDER_ID_PREFIX", "OE-S") . $participant->id;
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
                "team_category" => $event_category['team_category_id']
            ];
        }
        ArcheryEventParticipantMember::insert($member);

        $payment = PaymentGateWay::setTransactionDetail($total_price, $order_id)
            ->enabledPayments(["bca_va", "bni_va", "bri_va", "other_va", "gopay"])
            ->setCustomerDetails($user["name"], $user["email"], $user["phone_number"])
            ->addItemDetail($event->id, $total_price, $event->event_name)
            ->CreateSnap();

        $participant->transaction_log_id = $payment->transaction_log_id;
        $participant->save();

        return ["archery_event_participant_unique_id" => $participant->unique_id];
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
