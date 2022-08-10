<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VenuePlaceProductSession extends Model
{
    use SoftDeletes;

    protected $guarded = [];
}
