<?php

namespace App\BLoC\Web\ArcheryEventParticipant;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\TransactionLog;
use DAI\Utils\Abstracts\Retrieval;

class GetArcheryEventParticipantMember extends Retrieval
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
        $status = $parameters->get('status');

        $archery_event_participant = ArcheryEventParticipant::select("archery_event_participants.*", "transaction_logs.status")->join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
        ->where('archery_event_participants.event_id', $parameters->get('id'))
        ->where('archery_event_participants.team_category_id', $team_category_id)
        ->where('archery_event_participants.competition_category_id', $competition_category_id)
        ->where('archery_event_participants.competition_category_id', $competition_category_id);
        if (!is_null($status) && $status != 0) {
            $archery_event_participant->where('transaction_logs.status', $status);
        }

        $participant = $archery_event_participant->orderBy('archery_event_participants.created_at', 'DESC')->get();
        $participant_ids = [];
        $participants = [];
        foreach ($participant as $key => $value) {
            $participants[$value->id] = $value;
            $participant_ids []=$value->id;
        }

        $members = [];
        if($participant)
            $members = ArcheryEventParticipantMember::whereIn("archery_event_participant_id",$participant_ids)->get();
        
        $list = [];
        foreach ($members as $k => $v) {
            if(!isset($participants[$v->archery_event_participant_id])){
                continue;
            }
            $p = $participants[$v->archery_event_participant_id];
            $p->member = $v;
            $p->status_label = TransactionLog::getStatus($p->status); 
            $list [] = $p;
        }
        return $list;
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|exists:archery_events,id',
        ];
    }
}
