<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ArcheryClub extends Model
{
    protected $table = 'archery_clubs';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'place_name', 'province', 'city', 'logo', 'address', 'description', 'banner'];
    protected $appends = ['detail_province', 'detail_city', 'is_admin'];

    public function getDetailProvinceAttribute()
    {
        $province = Provinces::find($this->province);
        return $this->attributes['detail_province'] = $province ? $province : [];
    }

    public function getDetailCityAttribute()
    {
        $city = City::find($this->city);
        return $this->attributes['detail_city'] = $city ? $city : [];
    }

    public function getIsAdminAttribute()
    {
        $user = Auth::guard('app-api')->user();
        if ($user) {
            $member = ClubMember::where('user_id', $user->id)->where('club_id', $this->id)->first();
            if ($member && $member->role == 1) {
                return 1;
            } else {
                return 0;
            }
        }
        return 0;
    }
}
