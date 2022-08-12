<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CityCountry extends Model
{
    protected $table = 'cities_of_countries';

    public static function getDetailCityCountry($id)
    {
        $detail_city = null;

        $city_country = self::find($id);
        if ($city_country) {
            $detail_city = [
                "id" => $city_country->id,
                "name" => $city_country->name
            ];
        }

        return $detail_city;
    }
}
