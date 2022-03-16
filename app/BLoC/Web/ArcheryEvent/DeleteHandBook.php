<?php

namespace App\BLoC\Web\ArcheryEvent;

use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class DeleteHandBook extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();

        $event = ArcheryEvent::find($parameters->get('event_id'));
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        if ($admin->id != $event->admin_id) {
            throw new BLoCException("forbiden");
        }

        $event->handbook = null;
        $event->save();
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => [
                'required'
            ],
        ];
    }
}
