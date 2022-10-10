<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryConfigMappingArrowRambahan extends Model
{
    protected $table = "category_config_mapping_arrow_rambahan";
    protected $fillable = ["config_arrow_rambahan_id", "category_id"];
}
