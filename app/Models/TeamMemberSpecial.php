<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMemberSpecial extends Model
{
    protected $table = 'team_member_special';
    protected $guarded = ['id'];

    public static function deleteMemberSpecial($participant_team_id)
    {
        $team_member_special_list = TeamMemberSpecial::where("participant_team_id", $participant_team_id)
            ->get();
        foreach ($team_member_special_list as $tmsl) {
            $tmsl->delete();
        }
    }
}
