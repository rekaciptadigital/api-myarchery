<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryClub extends Model
{
    protected $table = 'archery_clubs';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'place_name', 'province', 'city', 'logo', 'address', 'description', 'banner'];

    public function user()
    {
        return $this->belongsToMany('App\Models\User', 'club_members', 'club_id', 'user_id')->withPivot('status');
    }
}
