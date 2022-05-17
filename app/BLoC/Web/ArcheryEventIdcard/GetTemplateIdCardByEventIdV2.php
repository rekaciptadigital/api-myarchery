<?php

namespace App\BLoC\Web\ArcheryEventIdcard;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventIdcardTemplate;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetTemplateIdCardByEventIdV2 extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $event_id = $parameters->get('event_id');

        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        if ($admin->id != $event->admin_id) {
            throw new BLoCException("forbiden");
        }

        $id_card_template = ArcheryEventIdcardTemplate::where("event_id", $event_id)->first();

        return $id_card_template;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required',
        ];
    }
}
