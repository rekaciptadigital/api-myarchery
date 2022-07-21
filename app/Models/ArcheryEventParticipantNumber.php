<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryEventParticipantNumber extends Model
{
    protected $table = 'archery_event_participant_numbers';

    protected $fillable = ['prefix', 'participant_id'];

    protected $primaryKey = "sequence";

    public static function getNumber($participant_id)
    {
        $number = "";
        $data = self::where('participant_id', $participant_id)->first();
        if ($data) {
            $number = $data->prefix . "-" . self::sequenceFormatNumber($data->sequence);
        }
        return $number;
    }

    public static function saveNumber($prefix, $participant_id)
    {
        return self::firstOrNew(array(
            'prefix' => $prefix,
            'participant_id' => $participant_id
        ))->save();
    }

    public static function makePrefix($event_category_id, $gender)
    {
        $g = $gender == "male" ? 1 : 2;
        return "MA-" . date("y") . "-" . $event_category_id . "-" . $g;
    }

    private static function sequenceFormatNumber($number)
    {
        if ($number <= 9) {
            $number = "00" . $number;
        } else if ($number <= 99 && $number > 9) {
            $number = "0" . $number;
        } else {
            $number = "" . $number;
        }
        return $number;
    }
}
