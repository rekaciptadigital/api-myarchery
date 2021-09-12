<?php

namespace App\BLoC\Web\ArcheryEventScoringSystem;

use App\Models\ArcheryEventScoringSystemCategory;
use App\Models\ArcheryEventScoringSystemDetail;
use DAI\Utils\Abstracts\Transactional;

class AddArcheryEventScoringSystem extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_event_category = $parameters->get('event_category');
        $archery_event_scoring_system_category = new ArcheryEventScoringSystemCategory();
        $archery_event_scoring_system_category->event_id = $parameters->get('id');
        $archery_event_scoring_system_category->team_category_id = $archery_event_category['team_category_id'];
        $archery_event_scoring_system_category->age_category_id = $archery_event_category['age_category_id'];
        $archery_event_scoring_system_category->competition_category_id = $archery_event_category['competition_category_id'];
        $archery_event_scoring_system_category->distance_id = $archery_event_category['distance_id'];
        $archery_event_scoring_system_category->save();

        $scoring_details = $parameters->get('scoring_details', []);
        foreach ($scoring_details as $scoring_detail) {
            $archery_event_scoring_system_detail = new ArcheryEventScoringSystemDetail();
            $archery_event_scoring_system_detail->archery_event_scoring_system_category_id = $archery_event_scoring_system_category->id;
            $archery_event_scoring_system_detail->total_session = $scoring_detail['total_session'];
            $archery_event_scoring_system_detail->round_type_id = $scoring_detail['round_type_id'];
            $archery_event_scoring_system_detail->total_end = $scoring_detail['total_end'];
            $archery_event_scoring_system_detail->total_shoot = $scoring_detail['total_shoot'];
            $archery_event_scoring_system_detail->target_face = $scoring_detail['target_face'];
            $archery_event_scoring_system_detail->save();
        }

        $archery_event_scoring_system_category->archeryEventScoringSystemDetails;
        return $archery_event_scoring_system_category;
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|exists:archery_events,id',
        ];
    }
}
