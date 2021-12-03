<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ArcheryEventParticipant extends Model
{
    public function archeryEventParticipantMembers()
    {
        return $this->hasMany(ArcheryEventParticipantMember::class, 'archery_event_participant_id', 'id');
    }
    public static function getMemberByUserId($user_id,$participant_id)
    {
      $archery_participant =DB::select('archery_event_participant_members.*','archery_event_participants.event_id')->table('archery_event_participants')
                            ->join('archery_event_participant_members','archery_event_participants.id','=','archery_event_participant_members.archery_event_participant_id')
                            ->where('archery_event_participant_members.user_id', $user_id)
                            ->where('archery_event_participants.id', $participant_id)
                            ->where('archery_event_participants.status', 1)
                            ->first();
      return $archery_participant;
    }
}
