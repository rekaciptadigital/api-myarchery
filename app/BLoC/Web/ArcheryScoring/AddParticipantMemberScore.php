<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryScoring;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryQualificationSchedules;
use App\Models\ArcheryEventEliminationMember;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Libraries\EliminationFormat;

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
        if($parameters->type == 2)
            return $this->addScoringElimination($parameters);
    }

    private function addScoringElimination($parameters){
        $elimination_id = $parameters->elimination_id;
        $round = $parameters->round;
        $match = $parameters->match;
        $type = $parameters->type;
        $save_permanent = $parameters->save_permanent;
        $members = $parameters->members;
        $valid = 1;
        $get_elimination = ArcheryEventElimination::find($elimination_id);
        if(!$get_elimination)
            throw new BLoCException("elimination tidak valid");

        $get_member_match = ArcheryEventEliminationMatch::select(
                            "archery_event_elimination_members.member_id",
                            "archery_event_elimination_matches.*"
                            )
                            ->join("archery_event_elimination_members","archery_event_elimination_matches.elimination_member_id","=","archery_event_elimination_members.id")
                            ->where("archery_event_elimination_matches.event_elimination_id",$elimination_id)
                            ->where("round",$round)
                            ->where("match",$match)
                            ->get();
        if(count($get_member_match) < 1)
            throw new BLoCException("match tidak valid");        
            
        foreach ($get_member_match as $key => $value) //check valid members 
        {
            if($value->win == 1)
                throw new BLoCException("match have winner");        

            if($value->member_id != $members[0]["member_id"] && $value->member_id != $members[1]["member_id"])
                $valid = 0;  
        }

        if(!$valid)
            throw new BLoCException("member tidak valid");
        if($get_elimination->elimination_scoring_type == 1)
            $calculate = ArcheryScoring::calculateEliminationScoringTypePointFormat($members[0],$members[1], $save_permanent);
        if($get_elimination->elimination_scoring_type == 2)
            $calculate = ArcheryScoring::calculateEliminationScoringTypeTotalFormat($members[0],$members[1], $save_permanent);
        foreach ($get_member_match as $key => $value) //check valid members 
        {
            $participant_member_id = $value->member_id;
            $scoring = $calculate[$participant_member_id]["scores"];
            $total = $scoring["total"];
            $win = $scoring["win"];
            $session = 1;
            $type = 2;
            $item_value = "archery_event_elimination_matches";
            $item_id = $value->id;
            $participant_scoring = ArcheryScoring::where("type",2)->where("item_id",$item_id)->first();
            if(!$participant_scoring)
                $participant_scoring = new ArcheryScoring;
            $participant_scoring->participant_member_id = $participant_member_id;
            $participant_scoring->total = $total;
            $participant_scoring->scoring_session = $session;
            $participant_scoring->type = $type;
            $participant_scoring->item_value = $item_value;
            $participant_scoring->item_id = $item_id;
            $participant_scoring->scoring_log = \json_encode($value);
            $participant_scoring->scoring_detail = \json_encode($scoring);
            $participant_scoring->save();
            if($save_permanent == 1){
                $champion = EliminationFormat::EliminationChampion($get_elimination->count_participant,$round,$match,$win);
                if($champion != 0){
                    ArcheryEventEliminationMember::where("id",$value->elimination_member_id)->update(["elimination_ranked"=>$champion]);
                }
                if($win == 1){
                    ArcheryEventEliminationMatch::where("id",$value->id)->update(["win"=>$win]);
                }
                $next = EliminationFormat::NextMatch($get_elimination->count_participant, $round, $match, $win);
                if(count($next) > 0){
                    ArcheryEventEliminationMatch::where("round",$next["round"])
                                                    ->where("match",$next["match"])
                                                    ->where("index",$next["index"])
                                                    ->where("event_elimination_id",$elimination_id)
                                                    ->update(["elimination_member_id"=>$value->elimination_member_id]);
                }
            }
        }
        return true;
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
        $scoring->total_tmp = $score->total_tmp_string;
        $scoring->scoring_session = $scoring_session;
        $scoring->type = $parameters->type;
        $scoring->item_value = "archery_qualification_schedules";
        $scoring->item_id = $schedule_member->id;
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
