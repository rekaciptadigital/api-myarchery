<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'cities';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = "char";


    public static function getDetailCity($id)
    {
        $detail_city = null;
        $city = self::find($id);
        if ($city) {
            $detail_city = [
                "id" => $city->id,
                "name" => $city->name
            ];
        }

        return $detail_city;
    }
}
