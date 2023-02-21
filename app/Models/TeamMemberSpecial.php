<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMemberSpecial extends Model
{
    protected $table = 'team_member_special';
    protected $guarded = ['id'];

    public static function deleteMemberSpecial(ArcheryEventParticipant $participant_team, $with_contingent)
    {
        $list_participant_team = ArcheryEventParticipant::where("status", 1)
            ->where("event_category_id", $participant_team->event_category_id)
            ->where("is_special_team_member", 1);

        if ($with_contingent == 1) {
            $list_participant_team = $list_participant_team->where("city_id", $participant_team->city_id);
        } else {
            $list_participant_team = $list_participant_team->where("club_id", $participant_team->club_id);
        }

        $list_participant_team = $list_participant_team->get();

        foreach ($list_participant_team as $lpt) {
            $lpt->is_special_team_member = 0;
            $lpt->save();

            $team_member_special_list = TeamMemberSpecial::where("participant_team_id", $lpt->id)
                ->get();
            foreach ($team_member_special_list as $tmsl) {
                $tmsl->delete();
            }
        }
    }
}
