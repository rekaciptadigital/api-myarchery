<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigCategoryRegister extends Model
{
    protected $table = "config_category_registers";

    protected $fillable = [
        "event_id",
        "team_category_id",
        "datetime_start_register",
        "datetime_end_register"
    ];
}
