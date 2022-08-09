<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provinces extends Model
{
    protected $table = 'provinces';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = "char";

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
