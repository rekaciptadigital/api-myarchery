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
            SELECT *,
                (SELECT COUNT(1) FROM archery_event_end_shoot_scores X1 WHERE X1.archery_event_end_score_id = C.id AND X1.point = 'X') as count_x,
                (SELECT COUNT(1) FROM archery_event_end_shoot_scores X2 WHERE X2.archery_event_end_score_id = C.id AND X2.point = '10') as count_x_10
            FROM archery_event_scores A
            JOIN archery_event_participant_members B ON A.archery_event_participant_member_id = B.id
            LEFT JOIN archery_event_end_scores C ON A.id = C.archery_event_score_id
            WHERE A.event_id = :event_id
        ";
        $query_params = [
            "event_id" => $parameters->get('id'),
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
