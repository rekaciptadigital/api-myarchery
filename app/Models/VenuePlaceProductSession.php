<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VenuePlaceProductSession extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function getListProductSessionByPlaceId($place_id)
    {
        $schedule_operationals = VenuePlaceScheduleOperational::where('place_id', $place_id)
                                    ->where('is_open', true)
                                    ->orderByRaw("FIELD(day, \"Senin\", \"Selasa\", \"Rabu\", \"Kamis\", \"Jumat\", \"Sabtu\", \"Minggu\")")
                                    ->get();
        if (!$schedule_operationals) throw new BLoCException("You haven't set operational schedule");

        $result = [];
        $session = [];
        foreach ($schedule_operationals as $operational) {
            $product_sessions = VenuePlaceProductSession::where('schedule_operational_id', $operational->id)->get();
            if (!$product_sessions) {
                return $schedule_operationals;
            } else {
                $result[$operational->day] = [];
                foreach ($product_sessions as $key) {
                    $session['start_time'] = $key->start_time;
                    $session['end_time'] = $key->end_time;
                    $session['capacity'] = $key->max_capacity;
                    array_push($result[$operational->day], $session);
                }
            }
        }
        return $result;
    }
}
