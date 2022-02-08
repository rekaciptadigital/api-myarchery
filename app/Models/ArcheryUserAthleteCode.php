<?php

namespace App\Models;

use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Database\Eloquent\Model;

class ArcheryUserAthleteCode extends Model
{
    protected $table = 'table_archery_user_athlete_code';
    protected $fillable = ['prefix', 'user_id',];


    public static function getAthleteCode($user_id)
    {
        $number = "";
        $data = self::where('user_id', $user_id)->first();
        if ($data) {
            $number = $data->prefix . "" . self::sequenceFormatNumber($data->sequence);
        }
        return $number;
    }

    public static function makePrefix($city_code)
    {   
        $y = date("y");
        $m = date("m");
        return $city_code . "" . $y . "" . $m;
    }

    public static function saveAthleteCode($prefix, $user_id)
    {
        return self::firstOrNew(array(
            'prefix' => $prefix,
            'user_id' => $user_id,
        ))->save();
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
