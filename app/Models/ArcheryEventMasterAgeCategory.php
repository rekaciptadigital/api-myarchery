<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryEventMasterAgeCategory extends Model
{
    public $incrementing = false;
    protected $table = 'archery_master_age_categories';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
}
