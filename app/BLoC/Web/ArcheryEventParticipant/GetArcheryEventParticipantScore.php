<?php

namespace App\BLoC\Web\ArcheryEventParticipant;

use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;

class GetArcheryEventParticipantScore extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $query = "
            SELECT *
            FROM archery_event_scores A
            JOIN archery_event_participant_members B ON A.archery_event_participant_member_id = B.id
            JOIN archery_event_end_scores C ON A.id = C.archery_event_score_id
            WHERE A.event_id = :id
        ";
        $query_params = [
            "id" => $parameters->get('id')
        ];

        $results = DB::select($query, $query_params);

        return $results;
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|exists:archery_events,id',
        ];
    }
}
