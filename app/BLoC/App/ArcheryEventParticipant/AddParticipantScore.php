<?php

namespace App\BLoC\App\ArcheryEventParticipant;

use App\Models\ArcheryEventEndScore;
use App\Models\ArcheryEventEndShootScore;
use App\Models\ArcheryEventScore;
use DAI\Utils\Abstracts\Transactional;

class AddParticipantScore extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get('event_id');
        $archery_event_participant_member_id = $parameters->get('archery_event_participant_member_id');
        $archery_event_scoring_system_category_id = $parameters->get('archery_event_scoring_system_category_id');
        $scorer_name = $parameters->get('scorer_name');
        $end = $parameters->get('end');

        $archery_event_score = ArcheryEventScore::where('event_id', $event_id)
            ->where('archery_event_participant_member_id', $archery_event_participant_member_id)
            ->where('archery_event_scoring_system_category_id', $archery_event_scoring_system_category_id)
            ->first();

        if (is_null($archery_event_score)) {
            $archery_event_score = new ArcheryEventScore();
            $archery_event_score->event_id = $event_id;
            $archery_event_score->archery_event_participant_member_id = $archery_event_participant_member_id;
            $archery_event_score->archery_event_scoring_system_category_id = $archery_event_scoring_system_category_id;
            $archery_event_score->scorer_name = $scorer_name;
            $archery_event_score->save();
        }

        $archery_event_end_score = ArcheryEventEndScore::where('archery_event_score_id')
            ->where('end', $end)
            ->first();

        if (is_null($archery_event_end_score)) {
            $archery_event_end_score = new ArcheryEventEndScore();
            $archery_event_end_score->archery_event_score_id = $archery_event_score->id;
            $archery_event_end_score->end = $end;
            $archery_event_end_score->total_score = 0;
            $archery_event_end_score->save();
        }

        $shoot_scores = $parameters->get('shoot_scores', []);
        $total_point = 0;
        foreach ($shoot_scores as $shoot_score) {
            $point = strtoupper($shoot_score['point']);
            $archery_event_end_shoot_score = new ArcheryEventEndShootScore();
            $archery_event_end_shoot_score->archery_event_end_score_id = $archery_event_end_score->id;
            $archery_event_end_shoot_score->point = $point;
            $archery_event_end_shoot_score->save();

            $integerPoint = $point === 'X' ? 10 : intval($point);
            $total_point += $integerPoint;
        }

        $archery_event_end_score->total_score = $total_point;
        $archery_event_end_score->save();

        return $archery_event_score;
    }
}
