<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryScoring;
use App\Models\ArcheryQualificationSchedules;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AddParticipantMemberScore extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $schedule_member = ArcheryQualificationSchedules::find($parameters->schedule_id);
        if($schedule_member->is_scoring == 1)
            throw new BLoCException("scoring sudah pernah ditambahkan pada jadwal ini");

        $score = ArcheryScoring::makeScoring($parameters->shoot_scores);
        $check_scoring_count = ArcheryScoring::where("participant_member_id",$schedule_member->participant_member_id)->count();
        $scoring_session = 1;
        if($check_scoring_count > 0){
            if($check_scoring_count >= 3)
                throw new BLoCException("peserta sudah melakukan 3x scoring");

            $scoring_session = $check_scoring_count + 1;
        }
        
        $scoring = new ArcheryScoring;
        $scoring->participant_member_id = $schedule_member->participant_member_id;
        $scoring->total = $score->total;
        $scoring->scoring_session = $scoring_session;
        $scoring->type = $parameters->type;
        $scoring->scoring_log = \json_encode(["admin" => $admin,
                                            "archery_qualification_schedules"=>$schedule_member,
                                            "target_no" => $parameters->target_no]);
        $scoring->scoring_detail = \json_encode($score->scors);
        $scoring->save();
        $schedule_member->is_scoring = 1;
        $schedule_member->save();

        $archery_event_score = ArcheryScoring::generateScoreBySession($schedule_member->participant_member_id,$parameters->type,[1,2,3]);
        $tmpScoring = $archery_event_score["sessions"];
        usort($tmpScoring, function($a, $b) {return $b["total_tmp"] > $a["total_tmp"] ? 1 : -1;});
        foreach ($tmpScoring as $k => $aes) {
            if(isset($aes["scoring_id"]))
            {$user_score = ArcheryScoring::find($aes["scoring_id"]);
            $user_score->scoring_session = $k+1;
            $user_score->save();}
        }
        return $scoring;
    }

    protected function validation($parameters)
    {
        return [
            'schedule_id' => 'required|exists:archery_qualification_schedules,id',
            'shoot_scores' => 'required',
            'target_no' => 'required'
        ];
    }
}
