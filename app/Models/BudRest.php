<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudRest extends Model
{
    protected $table = 'bud_rest';
    protected $primaryKey = 'id';
    protected $fillable = ['archery_event_category_id', 'bud_rest_start', 'bud_rest_end', 'target_face', 'type'];
}
