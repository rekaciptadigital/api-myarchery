<?php

namespace App\BLoC\Web\ArcheryEventCategories;

use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;

class GetArcheryEventCategory extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $query = "
            SELECT A.id as archery_event_id,
                B.age_category_id, B1.label as age_category_label,
                C.competition_category_id, C1.label as competition_category_label,
                D.team_category_id, D1.label as team_category_label,
                E.distance_id, E1.label as distance_label,
                F.price as flat_price, G.price
            FROM archery_events A
            JOIN archery_event_categories B ON A.id = B.event_id
            JOIN archery_event_category_competitions C ON B.id = C.event_category_id
            JOIN archery_event_category_competition_teams D ON C.id = D.event_category_competition_id
            JOIN archery_event_category_competition_distances E ON C.id = E.event_category_competition_id
            JOIN archery_master_age_categories B1 ON B.age_category_id = B1.id
            JOIN archery_master_competition_categories C1 ON C.competition_category_id = C1.id
            JOIN archery_master_team_categories D1 ON D.team_category_id = D1.id
            JOIN archery_master_distances E1 ON E.distance_id = E1.id
            JOIN archery_event_registration_fees F ON A.id = F.event_id AND F.registration_type_id = 'normal'
            JOIN archery_event_registration_fees_per_category G ON F.id = G.event_registration_fee_id AND D.team_category_id = G.team_category_id
            WHERE A.id = :event_id
            ORDER BY D1.label, B1.label, C1.label, E1.label
        ";

        $results = DB::select($query, ['event_id' => $parameters->get('id')]);

        return $results;
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|exists:archery_events,id',
        ];
    }
}
