<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMemberSpecial extends Model
{
    protected $table = 'team_member_special';
    protected $guarded = ['id'];

    public static function deleteMemberSpecial(ArcheryEventParticipant $participant_team)
    {
        $participant_team->is_special_team_member = 0;
        $participant_team->save();

        $team_member_special_list = TeamMemberSpecial::where("participant_team_id", $participant_team->id)
            ->get();
        foreach ($team_member_special_list as $tmsl) {
            $tmsl->delete();
        }
    }
}
