<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryEventParticipantMemberNumber extends Model
{
    protected $table = 'archery_event_participant_member_numbers';
    protected $fillable = ['prefix', 'user_id', 'event_id'];

    protected $primaryKey = "sequence";

    public static function getMemberNumber($event_id, $user_id)
    {
        $number = "";
        $data = self::where('user_id', $user_id)->where('event_id', $event_id)->first();
        if ($data) {
            $number = $data->prefix . "-" . self::sequenceFormatNumber($data->sequence);
        }
        return $number;
    }

    public static function saveMemberNumber($prefix, $user_id, $event_id)
    {
        return self::firstOrNew(array(
            'prefix' => $prefix,
            'user_id' => $user_id,
            'event_id' => $event_id,
        ))->save();
    }

    public static function makePrefix($event_id, $gender)
    {
        $g = $gender == "male" ? 1 : 2;
        return "MA-" . date("y") . "-" . $event_id . "-" . $g;
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
