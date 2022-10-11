<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryConfigMappingArrowRambahan extends Model
{
    protected $table = "category_config_mapping_arrow_rambahan";
    protected $fillable = ["competition_category_id", "config_category_id", "age_category_id", "distance_id"];
}
