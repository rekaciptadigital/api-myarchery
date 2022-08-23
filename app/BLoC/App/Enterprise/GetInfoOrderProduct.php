<?php

namespace App\BLoC\App\Enterprise;


use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use App\Models\VenuePlaceProduct;
use App\Models\VenuePlaceScheduleOperational;
use App\Models\VenuePlaceScheduleOperationalSession;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;

class GetInfoOrderProduct extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $venue_place_product = VenuePlaceProduct::find($parameters->get('id'));
        if (!$venue_place_product) throw new BLoCException("Data not found");

        $booking_date = $parameters->get('booking_date');
        $day = dayTranslate(date('l', strtotime($booking_date)));

        $schedule_operational = VenuePlaceScheduleOperational::where('place_id', $venue_place_product->place_id)->where('day', $day)->first();
        if (!$schedule_operational) throw new BLoCException("Schedule operational not found");

        if ($venue_place_product->has_session == true) {
            $operational_sessions = VenuePlaceScheduleOperationalSession::where('schedule_operational_id', $schedule_operational->id)->get();
            if (!$operational_sessions) throw new BLoCException("Data not found");

            $sessions = [];
            foreach($operational_sessions as $value) {
                $session['id'] = $value->id;
                $session['schedule_operational_id'] = $value->schedule_operational_id;
                $session['start_time'] = $value->start_time;
                $session['end_time'] = $value->end_time;
                $session['quota'] = $value->max_capacity;
                array_push($sessions, $session);
            }
        }

        $result['price'] = $this->getPriceProduct($day, $venue_place_product);
        $result['sessions'] = $sessions;

        return $result;
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|integer',
        ];
    }

    private function getPriceProduct($day, $venue_place_product)
    {
        return ($day == 'Sabtu' || $day == 'Minggu') ? $venue_place_product->weekend_price : $venue_place_product->weekday_price;
    }
}
