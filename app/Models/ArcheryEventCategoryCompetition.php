<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryEventCategoryCompetition extends Model
{
    public function archeryEventCategoryCompetitionTeams()
    {
        return $this->hasMany(ArcheryEventCategoryCompetitionTeam::class, 'event_category_competition_id', 'id');
    }

    public function getDistancesAttribute($value) {
        return json_decode($value);
    }
}
