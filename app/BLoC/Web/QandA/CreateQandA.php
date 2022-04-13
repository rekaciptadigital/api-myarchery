<?php

namespace App\BLoC\Web\QandA;

use App\Models\ArcheryEvent;
use App\Models\QandA;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class CreateQandA extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $event_id = $parameters->get("event_id");
        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException("forbiden");
        }

        $q_and_a = new QandA();
        $q_and_a->event_id = $event_id;
        $q_and_a->sort = $parameters->get("sort", 0);
        $q_and_a->question = $parameters->get("question");
        $q_and_a->answer = $parameters->get("answer");
        $q_and_a->is_hide = $parameters->get("is_hide");
        $q_and_a->save();

        return $q_and_a;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|integer",
            "question" => "required",
            "answer" => "required",
            "is_hide" => "required"
        ];
    }
}
