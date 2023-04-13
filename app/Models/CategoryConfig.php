<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryConfig extends Model
{
    protected $table = "category_config";
    protected $fillable = [
        "config_arrow_rambahan_id",
        "session", "arrow", "rambahan", "have_special_category"
    ];
}
