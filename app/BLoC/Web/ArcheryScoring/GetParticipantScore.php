<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryScoring;
use App\Models\ArcheryQualificationSchedules;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\DB;
use DAI\Utils\Abstracts\Retrieval;

class GetParticipantScore extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $team_category_id = $parameters->get('team_category_id');
        $competition_category_id = $parameters->get('competition_category_id');
        $age_category_id = $parameters->get('age_category_id');
        $gender = $parameters->get('gender');

        $archery_event_participant = ArcheryEventParticipantMember::select(
                                        "archery_event_participant_members.id",
                                        "archery_event_participant_members.name",
                                        "archery_event_participant_members.gender",
                                        "archery_event_participants.club"
                                    )->
                                    join("archery_event_participants","archery_event_participant_members.archery_event_participant_id","=","archery_event_participants.id")->
                                    join("transaction_logs","archery_event_participants.transaction_log_id","=","transaction_logs.id")->
                                    where('transaction_logs.status', 1)->
                                    where('archery_event_participants.event_id', $parameters->get('event_id'));
        if (!is_null($team_category_id)) {
            $archery_event_participant->where('archery_event_participants.team_category_id', $team_category_id);
        }
        if (!is_null($gender) && !empty($gender)) {
            $archery_event_participant->where('archery_event_participant_members.gender', $gender);
        }
        if (!is_null($competition_category_id)) {
            $archery_event_participant->where('archery_event_participants.competition_category_id', $competition_category_id);
        }
        if (!is_null($age_category_id)) {
            $archery_event_participant->where('archery_event_participants.age_category_id', $age_category_id);
        }

        $participants = $archery_event_participant->get();
        
        $archery_event_score = [];
        foreach ($participants as $key => $value) {
            $score = ArcheryScoring::generateScoreBySession($value->id,$parameters->get('type'));
            $score["member"] = $value;
            $archery_event_score[] = $score;
        }
        
        usort($archery_event_score, function($a, $b) {return $b["total_tmp"] > $a["total_tmp"] ? 1 : -1;});

        return $archery_event_score;
    }

    protected function validation($parameters)
    {
        return [
        ];
    }
}
