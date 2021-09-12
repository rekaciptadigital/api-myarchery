<?php

namespace App\BLoC\Web\ArcheryEventScoringSystem;

use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;

class GetArcheryEventScoringSytem extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $team_category_id = $parameters->get('team_category_id');
        $round_type_id = $parameters->get('round_type_id');
        $competition_category_id = $parameters->get('competition_category_id');

        $query = "
            SELECT A.*, B.*, B.id as scoring_system_detail_id,
                B1.label as age_category_label,
                C1.label as competition_category_label,
                D1.label as team_category_label,
                E1.label as distance_label
            FROM archery_event_scoring_system_categories A
            JOIN archery_event_scoring_system_details B ON A.id = B.archery_event_scoring_system_category_id
            JOIN archery_master_age_categories B1 ON A.age_category_id = B1.id
            JOIN archery_master_competition_categories C1 ON A.competition_category_id = C1.id
            JOIN archery_master_team_categories D1 ON A.team_category_id = D1.id
            JOIN archery_master_distances E1 ON A.distance_id = E1.id
            WHERE A.event_id = :id
        ";

        $query_params = ["id" => $parameters->get('id')];

        if (!is_null($team_category_id)) {
            $query .= "
                AND A.team_category_id = :team_category_id
            ";
            $query_params['team_category_id'] = $team_category_id;
        }
        if (!is_null($competition_category_id)) {
            $query .= "
                AND A.competition_category_id = :competition_category_id
            ";
            $query_params['competition_category_id'] = $competition_category_id;
        }
        if (!is_null($round_type_id)) {
            $query .= "
                AND B.round_type_id = :round_type_id
            ";
            $query_params['round_type_id'] = $round_type_id;
        }

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
