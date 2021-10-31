<?php

namespace App\BLoC\Web\EventElimination;

use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryQualificationSchedules;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;

class GetEventElimination extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
       
        $schedule = ArcheryQualificationSchedules::list($parameters->event_id);
        if($admin["id"] != $schedule["event"]->admin_id){
            throw new BLoCException("event tidak sesuai");
        };
        
        $output = array(
            "schedules" => $schedule["list"],
            "event" => $schedule["event"],
        );
        return $output;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
