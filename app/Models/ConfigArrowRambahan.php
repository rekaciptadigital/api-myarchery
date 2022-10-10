<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigArrowRambahan extends Model
{
    protected $table = "config_arrow_rambahan";
    protected $fillable = ["event_id", "type"];
}
