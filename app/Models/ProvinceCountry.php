<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProvinceCountry extends Model
{
    protected $table = 'states';
    protected $guarded = ["id"];

    public static function getDetailProvince($id)
    {
        $detail_province = null;
        $province = self::find($id);
        if ($province) {
            $detail_province = [
                "id" => $province->id,
                "name" => $province->name
            ];
        }

        return $detail_province;
    }
}
