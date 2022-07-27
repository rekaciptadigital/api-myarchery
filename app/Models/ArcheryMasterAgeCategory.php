<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryMasterAgeCategory extends Model
{
    protected $table = 'archery_master_age_categories';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    protected $appends = [
        'can_update'
    ];


    public function getCanUpdateAttribute()
    {
        $can_update = 1;
        $time_now = time();
        $participant_register = ArcheryEventParticipant::select("archery_event_participants.*")
            ->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
            ->where("age_category_id", $this->id)
            ->where(function ($query) use ($time_now) {
                $query->where("archery_event_participants.status", 1);
                $query->orWhere(function ($q) use ($time_now) {
                    $q->where("archery_event_participants.status", 4);
                    $q->where("transaction_logs.expired_time", ">", $time_now);
                });
            })->get();

        if ($participant_register->count() > 0) {
            $can_update = 0;
        }

        return $this->attributes['can_update'] = $can_update;
    }
}
