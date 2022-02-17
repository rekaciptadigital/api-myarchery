<?php

namespace App\BLoC\Web\ArcheryScoring;

use App\Models\ArcheryScoring;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryQualificationSchedules;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\DB;
use DAI\Utils\Abstracts\Retrieval;

class GetParticipantScoreQualification extends Retrieval
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
        $score_type = 1;
        $event_id = $parameters->get('event_id');
        $distance_id = $parameters->get('distance_id');
        $event_category_id = $parameters->get('event_category_id');
        $category_detail = ArcheryEventCategoryDetail::find($event_category_id);
        $session = [];
        for ($i=0; $i < $category_detail->session_in_qualification; $i++) { 
            $session[] = $i+1;
        }
        $qualification_rank = ArcheryScoring::getScoringRankByCategoryId($event_category_id,$score_type,$session);
        return $qualification_rank;
    }

    protected function validation($parameters)
    {
        return [
        ];
    }
}
