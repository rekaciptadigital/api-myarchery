<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryMasterAgeCategory extends Model
{
    protected $table = 'archery_master_age_categories';
    protected $guarded = ["id"];
    public $incrementing = false;
    protected $keyType = 'string';
    protected $appends = [
        'can_update'
    ];


    public function getCanUpdateAttribute()
    {
        $can_update = 1;

        if ($this->eo_id == 0) {
            $can_update = 0;
        }

        return $this->attributes['can_update'] = $can_update;
    }
}
