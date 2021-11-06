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
    public static function isParticipate($user_id,$event_id)
    {
      $archery_participant =DB::table('archery_event_participants')->select('name')->where('user_id', $user_id)->where('event_id', $event_id)->first();
      if(!$archery_participant){
        return false;
      }else{
        return $archery_participant;
      }
    }
}
