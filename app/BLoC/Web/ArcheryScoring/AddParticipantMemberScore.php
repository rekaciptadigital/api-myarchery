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
        if($parameters->type == 1)
            return $this->addScoringQualification($parameters);
    }

    private function addScoringQualification($parameters){
        $admin = Auth::user();
        $schedule_member = ArcheryQualificationSchedules::find($parameters->schedule_id);
        if($schedule_member->is_scoring == 1)
            throw new BLoCException("scoring sudah pernah ditambahkan pada jadwal ini");

        $score = ArcheryScoring::makeScoring($parameters->shoot_scores);
        $user_scores = ArcheryScoring::where("participant_member_id",$schedule_member->participant_member_id)->get();
        $check_scoring_count = 0;
        $event_score_id = 0;
        $scoring_session = 1;
        foreach ($user_scores as $key => $value) {
            $log = \json_decode($value->scoring_log); 
            if($log->archery_qualification_schedules->id == $parameters->schedule_id){
                $event_score_id = $value->id;
                $scoring_session = $value->scoring_session;
            }else{
                $check_scoring_count = $check_scoring_count + 1;
            }
        }

        if($check_scoring_count > 0){
            if($check_scoring_count >= 3)
                throw new BLoCException("peserta sudah melakukan 3x scoring");

            if($event_score_id == 0)
                $scoring_session = $check_scoring_count + 1;

            if($check_scoring_count == 2){
                $archery_event_score = ArcheryScoring::generateScoreBySession($schedule_member->participant_member_id,$parameters->type,[1,2,3]);
                $tmpScoring = $archery_event_score["sessions"];
                usort($tmpScoring, function($a, $b) {return $b["total_tmp"] < $a["total_tmp"] ? 1 : -1;});
                    foreach ($tmpScoring as $key => $value) {
                        if(($scoring_session == 3 && $value["session"] != 3 && $value["total_tmp"] < $score->total_tmp)
                            ||($scoring_session < 3 && $value["session"] == 3 && $value["total_tmp"] > $score->total_tmp)){
                            if(isset($value["scoring_id"])){
                            {
                                $user_score = ArcheryScoring::find($value["scoring_id"]);
                                $tmp_session = $user_score->scoring_session;
                                $user_score->scoring_session = $scoring_session;
                                $user_score->save();
                                $scoring_session = $tmp_session;
                                break;
                            }
                        }
                    }
                }
            }
        }
        if($event_score_id){
            $scoring = ArcheryScoring::find($event_score_id);
        }else{
            $scoring = new ArcheryScoring;
        }

        $scoring->participant_member_id = $schedule_member->participant_member_id;
        $scoring->total = $score->total;
        $scoring->scoring_session = $scoring_session;
        $scoring->type = $parameters->type;
        $scoring->scoring_log = \json_encode(["admin" => $admin,
                                            "archery_qualification_schedules"=>$schedule_member,
                                            "target_no" => $parameters->target_no]);
        $scoring->scoring_detail = \json_encode($score->scors);
        $scoring->save();

        if($parameters->save_permanent){
            $schedule_member->is_scoring = 1;
            $schedule_member->save();
        }

        return $scoring;
    }

    protected function validation($parameters)
    {
        if($parameters->type == 1)
        return [
            'schedule_id' => 'required|exists:archery_qualification_schedules,id',
            'shoot_scores' => 'required',
            'target_no' => 'required'
        ];

        return [];
    }
}
