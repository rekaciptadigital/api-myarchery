<?php

namespace App\BLoC\Web\ArcheryEvent;

use App\Models\ArcheryEvent;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UpdateArcheryEventStatus extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $archery_event = ArcheryEvent::find($parameters->get('id'));
        if (!$archery_event) {
            throw new BLoCException("Data not found");
        }

        if ($archery_event->admin_id != $admin->id) {
            throw new BLoCException("You're not the owner of this event");
        }

        $archery_event->status = $parameters->get('status');
        $archery_event->save();

        return $archery_event;
    }

    protected function validation($parameters)
    {
        return [
            'id' => 'required|integer',
        ];
    }
}
