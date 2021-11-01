<?php

namespace App\BLoC\Web\EventElimination;

use App\Models\ArcheryEventEliminationSchedule;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class SetEventEliminationSchedule extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        // TODO Tambahkan pengecekan event own
        $add = ArcheryEventEliminationSchedule::insert([
                                                        "event_id"=>$parameters->event_id,
                                                        "date"=>$parameters->date,
                                                        "start_time"=>$parameters->start_time,
                                                        "end_time"=>$parameters->end_time
                                                        ]);
        return $add;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required|exists:archery_events,id',
            'date' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
        ];
    }
}
