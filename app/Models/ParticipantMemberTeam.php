<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParticipantMemberTeam extends Model
{
    protected $table = 'participant_member_teams';
    protected $primaryKey = 'id';
    public static   $participant, $participant_member, $event_category_detail;

    protected $guarded = ['id'];

    public static function insertParticipantMemberTeam(
        $participant,
        $participant_member,
        $event_category_detail
    ) {
        return self::create([
            'event_category_id' => $event_category_detail->id,
            'participant_id' => $participant->id,
            'participant_member_id' => $participant_member->id,
            'type' => $event_category_detail->category_team
        ]);
    }

    public static function saveParticipantMemberTeam(
        $event_category_id
        $participant_id,
        $participant_member_id,
        $type
    ) {
        return self::create([
            'event_category_id' => $event_category_id,
            'participant_id' => $participant_id,
            'participant_member_id' => $participant_member_id,
            'type' => $type
        ]);
    }
}
