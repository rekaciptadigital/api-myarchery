<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ArcheryEventEndScore extends Model
{
    protected $appends = ['count_x', 'count_x_10'];

    public function archeryEventEndShootScores()
    {
        return $this->hasMany(ArcheryEventEndShootScore::class, 'archery_event_end_score_id', 'id');
    }

    public function getCountXAttribute()
    {
        $query = "
            SELECT COUNT(1) as count
            FROM archery_event_end_shoot_scores
            WHERE archery_event_end_score_id = :id
            AND point = 'X'
        ";
        $results = DB::select($query, ['id' => $this->id]);
        $result = collect($results)->first();
        return $result->count;
    }

    public function getCountX10Attribute()
    {
        $query = "
            SELECT COUNT(1) as count
            FROM archery_event_end_shoot_scores
            WHERE archery_event_end_score_id = :id
            AND point = '10'
        ";
        $results = DB::select($query, ['id' => $this->id]);
        $result = collect($results)->first();
        return $result->count;
    }
}
