<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryScoring;
use App\Models\ArcheryQualificationSchedules;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\DB;

class AddParticipantMemberScore extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $schedule_member = ArcheryQualificationSchedules::find($parameters->schedule_id);
       
        $score = ArcheryScoring::makeScoring($parameters->shoot_scores);
        $check_scoring_count = ArcheryScoring::where("participant_member_id",$schedule_member->participant_member_id)->count();
        $scoring_session = 1;
        if($check_scoring_count > 0){
            $scoring_session = $check_scoring_count + 1;
            if($check_scoring_count == 2){
                $check_scoring_minimum = ArcheryScoring::where("participant_member_id",$schedule_member->participant_member_id)
                                        ->min("total");
            }
        }
        $scoring = new ArcheryScoring;
        $scoring->participant_member_id = $schedule_member->participant_member_id;
        $scoring->total = $score->total;
        $scoring->scoring_session = $scoring_session;
        $scoring->type = $parameters->type;
        $scoring->scoring_log = \json_encode([
                                            "archery_qualification_schedules"=>$schedule_member,
                                            "target_no" => $parameters->target_no]);
        $scoring->scoring_detail = \json_encode($score->scors);
        $scoring->save();
        return $scoring;
    }

    protected function validation($parameters)
    {
        return [
            'schedule_id' => 'required|exists:archery_qualification_schedules,id',
            'shoot_scores' => 'required'
        ];
    }
}
