<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryScoring;
use App\Models\ArcheryQualificationSchedules;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\DB;
use DAI\Utils\Abstracts\Retrieval;

class FindParticipantScoreBySchedule extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $schedule_member = ArcheryQualificationSchedules::find($parameters->schedule_id);
        $user_scores = ArcheryScoring::where("participant_member_id",$schedule_member->participant_member_id)->get();
        $session = count($user_scores) + 1;
        $score = (object)array();
        foreach ($user_scores as $key => $value) {
            $log = \json_decode($value->scoring_log); 
            if($log->archery_qualification_schedules->id == $parameters->schedule_id){
                $score = $value;
                $session = $value->scoring_session;
            }
        }
        $output = (object)array();
        $s = isset($score->scoring_detail) ? ArcheryScoring::makeScoringFormat(\json_decode($score->scoring_detail)) : ArcheryScoring::makeScoringFormat((object) array());
        $output->participant = ArcheryEventParticipantMember::memberDetail($schedule_member->participant_member_id);
        $output->score = $s;
        $output->session = $session;
        return $output;
    }

    protected function validation($parameters)
    {
        return [
            'schedule_id' => 'required|exists:archery_qualification_schedules,id',
        ];
    }
}
