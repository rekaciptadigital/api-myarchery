<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryEventCategory extends Model
{
    public function archeryEventCategoryCompetitions()
    {
        return $this->hasMany(ArcheryEventCategoryCompetition::class, 'event_category_id', 'id');
    }
}
