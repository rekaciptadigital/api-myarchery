<?php

namespace App\BLoC\Web\ArcheryReport;

use App\Models\ArcheryEventEliminationMember;
use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\ArcheryMasterTeamCategory;
use App\Models\ArcheryScoring;
use Illuminate\Support\Facades\Storage;
use PDFv2;
use Illuminate\Support\Facades\Redis;
use App\Models\ArcheryEventEliminationGroup;
use App\Models\ArcheryEventEliminationGroupMatch;
use App\Models\ArcheryEventEliminationGroupTeams;
use App\Libraries\ClubRanked;
use App\Models\ArcheryEventQualificationTime;
use DateTime;
use Illuminate\Support\Carbon;

class Upp extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {

        $today = "2022-07-2";
        $event_id = $parameters->get('event_id');
        return ArcheryEventQualificationTime::getCategoryByDate($today, $event_id);
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => 'required|integer'
        ];
    }
}
