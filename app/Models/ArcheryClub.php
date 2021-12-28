<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryClub extends Model
{
    protected $table = 'archery_clubs';
    protected $fillable = ['name', 'place_name', 'province', 'city', 'logo', 'address', 'description', 'banner'];
}
