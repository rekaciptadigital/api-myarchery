<?php

namespace App\Models;

use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Database\Eloquent\Model;
use App\Models\City;

class ArcheryUserAthleteCode extends Model
{
    protected $table = 'archery_user_athlete_codes';
    protected $fillable = ['prefix', 'user_id','city_code'];


    public static function getAthleteCode($user_id,$type = "my_archery")
    {
        $number = "";
        $data = self::where('user_id', $user_id)->where("status", 1)->first();
        if ($data) {
            $number = $data->prefix . "" . self::sequenceFormatNumber($data->sequence);
        }else{
            return "";
        }

        if($type == "perpani"){
            $city = City::where("prefix",$data->city_code)->first();
            if(empty($city->perpani_code)){
                return "";
            }

            return str_replace($city->prefix,$city->perpani_code,$number);
        }
        return $number;
    }

    public static function makePrefix($city_code)
    {   
        $y = date("Y");
        $m = date("m");
        return $city_code . "" . $y . "" . $m;
    }

    public static function saveAthleteCode($prefix, $user_id, $city_code)
    {
        return self::firstOrNew(array(
            'prefix' => $prefix,
            'user_id' => $user_id,
            'city_code' => $city_code,
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
