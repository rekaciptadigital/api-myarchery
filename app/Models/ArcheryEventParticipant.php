<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryEventParticipant extends Model
{
    public function archeryEventParticipantMembers()
    {
        return $this->hasMany(ArcheryEventParticipantMember::class, 'archery_event_participant_id', 'id');
    }
}
