<?php

namespace App\BLoC\Web\QandA;

use App\Models\ArcheryEvent;
use App\Models\QandA;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class DeleteQandA extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $event_id = $parameters->get("event_id");
        $q_and_a_id = $parameters->get("q_and_a_id");

        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException("forbiden");
        }

        $q_and_a = QandA::find($q_and_a_id);
        if (!$q_and_a) {
            throw new BLoCException("data tidak ditemukan");
        }

        $q_and_a->delete();

        return "success deleted";
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|integer",
            "q_and_a_id" => "required|integer",
        ];
    }
}
