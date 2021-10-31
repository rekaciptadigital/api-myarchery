<?php

namespace App\BLoC\Web\EventElimination;

use App\Models\ArcheryEventEliminationSchedule;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class RemoveEventEliminationSchedule extends Transactional
{
    public function getDescription()
    {
        return "";
    }
    
    protected function process($parameters)
    {
        // TODO Tambahkan pengecekan event own
        $remove = ArcheryEventEliminationSchedule::find($parameters->schedule_id);
        $remove->delete();
        return $remove;
    }

    protected function validation($parameters)
    {
        return [
            'schedule_id' => 'required|exists:archery_event_elimination_schedules,id',
        ];
    }
}
