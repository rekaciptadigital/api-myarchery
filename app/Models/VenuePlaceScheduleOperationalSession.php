<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class VenuePlaceScheduleOperationalSession extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function getListScheduleOperationalSessionByPlaceId($place_id)
    {
        $schedule_operationals = VenuePlaceScheduleOperational::select(DB::RAW('distinct day as day'), 'id')
                                    ->where('place_id', $place_id)
                                    ->where('is_open', true)
                                    ->orderByRaw("FIELD(day, \"Senin\", \"Selasa\", \"Rabu\", \"Kamis\", \"Jumat\", \"Sabtu\", \"Minggu\")")
                                    ->get();
        if (!$schedule_operationals) throw new BLoCException("You haven't set operational schedule");

        $result = [];
        $session = [];
        foreach ($schedule_operationals as $operational) {
            $session_settings = VenuePlaceScheduleOperationalSession::where('schedule_operational_id', $operational->id)->get();
                $result[$operational->day]['id'] = $operational->id;

                if (sizeof($session_settings) == 0) {
                    $result[$operational->day]['data'] = [];
                } else {
                    $sessions = [];
                    foreach ($session_settings as $key) {
                        $session['id'] = $key->id;
                        $session['schedule_operational_id'] = $key->schedule_operational_id;
                        $session['start_time'] = $key->start_time;
                        $session['end_time'] = $key->end_time;
                        $session['total_budrest'] = $key->total_budrest;
                        $session['total_target'] = $key->total_target;
                        $session['max_capacity'] = $key->max_capacity;
                        array_push($sessions, $session);
                    }
                    $result[$operational->day]['data'] = $sessions;
                }
                
        }
        return $result;
    }
}
