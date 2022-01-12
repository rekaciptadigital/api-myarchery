<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryEventParticipantMemberNumber extends Model
{
    protected $table = 'archery_event_participant_member_numbers';
    protected $fillable = ['prefix', 'participant_member_id'];

    public static function getMemberNumber($prefix, $participant_member_id)
    {
        return self::where('prefix', $prefix)->where('participant_member_id', $participant_member_id)->first();
    }

    public static function saveMemberNumber($prefix, $participant_member_id)
    {
        return self::firstOrNew(array(
            'prefix' => $prefix,
            'participant_member_id' => $participant_member_id,
        ))->save();
    }
}