<?php

namespace App\BLoC\Web\EventOrder;

use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventRegistrationFeePerCategory;
use Illuminate\Support\Facades\DB;

class GetEventPrice extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event = ArcheryEvent::find($parameters->event_id);
        $event_category = $parameters->get('category_event');

        $archery_event_price__normal_query = "
            SELECT A.*, B.price, B.id as registration_fee_id,
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
            SELECT A.*, B.price, B.id as registration_fee_id,
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

        $date_now = date("Y-m-d");

        if ($event->is_flat_registration_fee) {
            $normal_price_result = collect($archery_event_price_normal_results)->first();
            if (!is_null($normal_price_result)) {
                $total_price = $normal_price_result->price;
            }

            if (count($archery_event_price_early_bird_results) > 0) {
                $early_bird_price_result = collect($archery_event_price_early_bird_results)->first();
                if (!is_null($early_bird_price_result)) {
                    $early_bird_start_date = $early_bird_price_result->early_bird_start_date;
                    $early_bird_end_date = $early_bird_price_result->early_bird_end_date;
                    if ($early_bird_start_date <= $date_now && $date_now <= $early_bird_end_date) {
                        if (!is_null($early_bird_price_result)) {
                            $total_price = $early_bird_price_result->price;
                        }
                    }
                }
            }
        } else {
            $team_category_id = $event_category['team_category_id'];

            $normal_price_result = collect($archery_event_price_normal_results)->first();
            $normal_price_for_category = ArcheryEventRegistrationFeePerCategory::where('event_registration_fee_id', $normal_price_result->registration_fee_id)->where('team_category_id', $team_category_id)->first();
            if (!is_null($normal_price_for_category)) {
                $total_price = $normal_price_for_category->price;
            }

            if (count($archery_event_price_early_bird_results) > 0) {
                $early_bird_price_result = collect($archery_event_price_early_bird_results)->first();
                if (!is_null($early_bird_price_result)) {
                    $early_bird_price_for_category = ArcheryEventRegistrationFeePerCategory::where('event_registration_fee_id', $early_bird_price_result->registration_fee_id)->where('team_category_id', $team_category_id)->first();

                    $early_bird_start_date = $early_bird_price_result->early_bird_start_date;
                    $early_bird_end_date = $early_bird_price_result->early_bird_end_date;
                    if ($early_bird_start_date <= $date_now && $date_now <= $early_bird_end_date) {
                        if (!is_null($early_bird_price_for_category)) {
                            $total_price = $early_bird_price_for_category->price;
                        }
                    }
                }
            }
        }

        return $total_price;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
