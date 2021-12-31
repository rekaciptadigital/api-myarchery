<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryClub extends Model
{
    protected $table = 'archery_clubs';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'place_name', 'province', 'city', 'logo', 'address', 'description', 'banner'];
    protected $appends = ['detail_province', 'detail_city'];

    public function getDetailProvinceAttribute()
    {
        return $this->attributes['detail_province'] = Provinces::find($this->province);
    }

    public function getDetailCityAttribute()
    {
        return $this->attributes['detail_city'] = City::find($this->city);
    }
}
