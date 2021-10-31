<?php

namespace App\BLoC\Web\EventElimination;

use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryEventEliminationSchedule;
use App\Models\ArcheryEventCategoryDetail;
use Illuminate\Support\Facades\DB;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;
use App\Models\ArcheryScoring;

class GetEventEliminationSchedule extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $schedule = ArcheryEventEliminationSchedule::where("event_id",$parameters->get("event_id"))->orderBy("date","ASC")->get();
        return $schedule;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
