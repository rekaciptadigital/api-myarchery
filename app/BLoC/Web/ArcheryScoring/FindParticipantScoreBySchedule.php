<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryScoring;
use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationMatch;
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
        $type = $parameters->type ? $parameters->type : 1;
        if($type == 1){
            return $this->qualification($parameters);
        }

        if($type == 2){
            return $this->elimination($parameters);
        }
    }

    private function qualification($parameters){
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
        $output->is_updated = $schedule_member->is_scoring == 1 ? 0 : 1;
        return $output;
    }

    private function elimination($parameters){
        $elimination_id = $parameters->elimination_id;
        $match = $parameters->match;
        $round = $parameters->round;
        $scores = [];
        
        $elimination = ArcheryEventElimination::find($elimination_id);
        $members = ArcheryEventEliminationMatch::select(
            "archery_event_elimination_members.member_id",
            "archery_event_elimination_matches.*"
        )
        ->join("archery_event_elimination_members","archery_event_elimination_matches.elimination_member_id","=","archery_event_elimination_members.id")
        ->where("archery_event_elimination_matches.match",$match)
        ->where("archery_event_elimination_matches.round",$round)
        ->where("archery_event_elimination_matches.event_elimination_id",$elimination_id)->get();

        foreach ($members as $key => $value) {
            $output = (object)array();
            $score = (object)array();
            $member_score = ArcheryScoring::where("item_value","archery_event_elimination_matches")
                                            ->where("item_id",$value->id)
                                            ->where("participant_member_id",$value->member_id)
                                            ->first();
            if(!$member_score){
                if($elimination->elimination_scoring_type == 1)
                    $s = ArcheryScoring::makeEliminationScoringTypePointFormat();
                if($elimination->elimination_scoring_type == 2)
                    $s = ArcheryScoring::makeEliminationScoringTypeTotalFormat();
            }else{
                $s = \json_decode($member_score->scoring_detail);
            }
            $output->participant = ArcheryEventParticipantMember::memberDetail($value->member_id);
            $output->scores = $s;
            $output->session = $round;
            $output->is_updated = 1;
            $scores [] = $output;
        }
        
        return $scores;
    }

    protected function validation($parameters)
    {
        if(!$parameters->type || $parameters->type == 1)
        return [
            'schedule_id' => 'required|exists:archery_qualification_schedules,id',
        ];

        return [];
    }
}
