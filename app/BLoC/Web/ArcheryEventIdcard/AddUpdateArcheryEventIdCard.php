<?php

namespace App\BLoC\Web\ArcheryEventIdcard;

use App\Models\ArcheryEventIdcardTemplate;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AddUpdateArcheryEventIdCard extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();

        $archery_event_id_card = ArcheryEventIdcardTemplate::where('event_id', $parameters->get('event_id'))->get();
        if ($archery_event_id_card->isEmpty()) {
            $archery_event_id_card = new ArcheryEventIdcardTemplate();
            $archery_event_id_card->event_id = $parameters->get('event_id');
            $archery_event_id_card->html_template = $parameters->get('html_template');
            $archery_event_id_card->editor_data = $parameters->get('editor_data');
            $archery_event_id_card->background  = $parameters->get('background');
            $archery_event_id_card->logo_event  = $parameters->get('logo_event');
            $archery_event_id_card->save();
        } else {
            $archery_event_id_card = ArcheryEventIdcardTemplate::find($archery_event_id_card[0]['id']);
            $archery_event_id_card->event_id = $parameters->get('event_id');
            $archery_event_id_card->html_template = $parameters->get('html_template');
            $archery_event_id_card->editor_data = $parameters->get('editor_data');
            $archery_event_id_card->background  = $parameters->get('background');
            $archery_event_id_card->logo_event  = $parameters->get('logo_event');
            $archery_event_id_card->save();
        }

        return $archery_event_id_card;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required',
            'html_template' => 'required',
        ];
    }
}
