<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryEventScoringSystemCategory extends Model
{
    public function archeryEventScoringSystemDetails() {
        return $this->hasMany(ArcheryEventScoringSystemDetail::class, 'archery_event_scoring_system_category_id', 'id');
    }
}
