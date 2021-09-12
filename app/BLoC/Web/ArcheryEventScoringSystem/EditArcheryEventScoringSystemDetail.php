<?php

namespace App\BLoC\Web\ArcheryEventScoringSystem;

use App\Models\ArcheryEventScoringSystemDetail;
use DAI\Utils\Abstracts\Retrieval;

class EditArcheryEventScoringSystemDetail extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_event_scoring_system_detail = ArcheryEventScoringSystemDetail::find();
        $archery_event_scoring_system_detail->total_session = $parameters->get('total_session');
        $archery_event_scoring_system_detail->round_type_id = $parameters->get('round_type_id');
        $archery_event_scoring_system_detail->total_end = $parameters->get('total_end');
        $archery_event_scoring_system_detail->total_shoot = $parameters->get('total_shoot');
        $archery_event_scoring_system_detail->target_face = $parameters->get('target_face');
        $archery_event_scoring_system_detail->save();

        return $archery_event_scoring_system_detail;
    }

    protected function validation($parameters)
    {
        return [
            'scoring_system_detail_id' => 'required|exists:archery_event_scoring_system_details,id',
        ];
    }
}
