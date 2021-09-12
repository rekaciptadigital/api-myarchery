<?php

namespace App\BLoC\Web\ArcheryEventParticipant;

use App\Models\ArcheryEventParticipant;
use DAI\Utils\Abstracts\Retrieval;

class GetArcheryEventParticipant extends Retrieval
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

        $archery_event_participant = ArcheryEventParticipant::where('event_id', $parameters->get('id'));
        if (!is_null($team_category_id)) {
            $archery_event_participant->where('team_category_id', $team_category_id);
        }
        if (!is_null($competition_category_id)) {
            $archery_event_participant->where('competition_category_id', $competition_category_id);
        }
        if (!is_null($age_category_id)) {
            $archery_event_participant->where('age_category_id', $age_category_id);
        }

        return $archery_event_participant->orderBy('created_at', 'DESC')->get();
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|exists:archery_events,id',
        ];
    }
}
