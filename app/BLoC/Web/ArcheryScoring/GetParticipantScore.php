<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryScoring;
use App\Models\ArcheryQualificationSchedules;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\DB;
use DAI\Utils\Abstracts\Retrieval;

class GetParticipantScore extends Retrieval
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
        $gender = $parameters->get('gender');
        $score_type = $parameters->get('type');
        $event_id = $parameters->get('event_id');
        $distance_id = $parameters->get('distance_id');

        $qualification_rank = ArcheryScoring::getScoringRank($distance_id,$team_category_id,$competition_category_id,$age_category_id,$gender,$score_type,$event_id);
        return $qualification_rank;
    }

    protected function validation($parameters)
    {
        return [
        ];
    }
}
