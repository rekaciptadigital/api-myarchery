<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserArcheryInfo extends Model
{
    protected $table = 'user_archery_info';

    public function archeryCategory() {
        return $this->belongsTo(ArcheryCategory::class);
    }

    public function archeryClub() {
        return $this->belongsTo(archeryClub::class);
    }
}
