<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';

    public static function getDetailCountry($id)
    {
        $detail_country = null;

        $country = self::find($id);
        if ($country) {
            $detail_country = [
                "id" => $country->id,
                "name" => $country->name
            ];
        }

        return $detail_country;
    }
}
