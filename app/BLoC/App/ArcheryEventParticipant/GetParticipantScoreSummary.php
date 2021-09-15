<?php

namespace App\BLoC\App\ArcheryEventParticipant;

use App\Models\ArcheryEventScore;
use DAI\Utils\Abstracts\Retrieval;

class GetParticipantScoreSummary extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_event_score = ArcheryEventScore::where('archery_event_participant_member_id', $parameters->get('archery_event_participant_member_id'))->first();
        $archery_event_end_scores = $archery_event_score->archeryEventEndScores;
        foreach ($archery_event_end_scores as $archery_event_end_score) {
            $archery_event_end_score->archeryEventEndShootScores;
        }

        return $archery_event_score;
    }

    protected function validation($parameters)
    {
        return [
            'unique_id' => 'required|exists:archery_event_participants,unique_id',
            'archery_event_participant_member_id' => 'required|exists:archery_event_participant_members,id',
        ];
    }
}
