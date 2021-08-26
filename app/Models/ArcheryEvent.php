<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryEvent extends Model
{

    public function archeryEventCategories() {
        return $this->hasMany(ArcheryEventCategory::class);
    }
}
