<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryEventScore extends Model
{
    public function archeryEventEndScores() {
        return $this->hasMany(ArcheryEventEndScore::class, 'archery_event_score_id', 'id');
    }
}
