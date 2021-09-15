<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Abstracts\Transactional;
use App\Libraries\PaymentGateWay;
use App\Models\ArcheryEventRegistrationFeePerCategory;
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
        $user = Auth::guard('app-api')->user();
        $event = ArcheryEvent::find($parameters->event_id);
        $total_price = 0;

        $event_category = $parameters->get('category_event');

        $total_price = $this->getPrice($event, $parameters);

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

    private function getPrice($event, $parameters)
    {
        $event_category = $parameters->get('category_event');

        $archery_event_price__normal_query = "
            SELECT A.*, B.price,
                B.start_date as early_bird_start_date,
                B.end_date as early_bird_end_date,
                B.registration_type_id
            FROM archery_events A
            JOIN archery_event_registration_fees B ON A.id = B.event_id
            WHERE A.id = :event_id
            AND B.registration_type_id = 'normal'
        ";
        $archery_event_price_normal_results = DB::SELECT($archery_event_price__normal_query, [
            "event_id" => $parameters->event_id
        ]);

        $archery_event_price__early_bird_query = "
            SELECT A.*, B.price,
                B.start_date as early_bird_start_date,
                B.end_date as early_bird_end_date,
                B.registration_type_id
            FROM archery_events A
            JOIN archery_event_registration_fees B ON A.id = B.event_id
            WHERE A.id = :event_id
            AND B.registration_type_id = 'early_bird'
        ";

        $archery_event_price_early_bird_results = DB::SELECT($archery_event_price__early_bird_query, [
            "event_id" => $parameters->event_id
        ]);

        $total_price = 0;

        if ($event->is_flat_registration_fee) {
            if (count($archery_event_price_early_bird_results) > 0) {
                $early_bird_price_result = collect($archery_event_price_early_bird_results)->first();
                $total_price = $early_bird_price_result->price;
            } else {
                $normal_price_result = collect($archery_event_price_normal_results)->first();
                $total_price = $normal_price_result->price;
            }
        } else {
            $team_category_id = $event_category['team_category_id'];

            $normal_price_result = collect($archery_event_price_normal_results)->first();
            $normal_price_for_category = ArcheryEventRegistrationFeePerCategory::where('event_registration_fee_id', $normal_price_result->id)->where('team_category_id', $team_category_id)->first();
            $total_price = $normal_price_for_category->price;

            if (count($archery_event_price_early_bird_results) > 0) {
                $early_bird_price_result = collect($archery_event_price_early_bird_results)->first();
                $early_bird_price_for_category = ArcheryEventRegistrationFeePerCategory::where('event_registration_fee_id', $early_bird_price_result->id)->where('team_category_id', $team_category_id)->first();

                $date_now = date("Y-m-d");
                $early_bird_start_date = $early_bird_price_for_category->early_bird_start_date;
                $early_bird_end_date = $early_bird_price_for_category->early_bird_end_date;
                if ($early_bird_start_date <= $date_now && $date_now <= $early_bird_end_date) {
                    $total_price = $early_bird_price_for_category->price;
                }
            }
        }

        return $total_price;
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
