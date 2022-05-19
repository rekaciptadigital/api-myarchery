<?php

namespace App\BLoC\Web\ArcheryEventIdcard;

use App\Libraries\Upload;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventIdcardTemplate;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class CreateOrUpdateIdCardTemplateV2 extends Transactional
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

        if ($parameters->get('background_url') != null) {
            $bacground = Upload::setPath("asset/background_id_card/")->setFileName("background_id_card_" . $event_id)->setBase64($parameters->get('background_url'))->save();
        }

        $id_card_template = ArcheryEventIdcardTemplate::where("event_id", $event_id)->first();
        if (!$id_card_template) {
            $id_card_template = new ArcheryEventIdcardTemplate;
        }

        $id_card_template->event_id = $event_id;
        $id_card_template->html_template =  $parameters->get('html_template');
        $id_card_template->editor_data = $parameters->get('editor_data');
        if ($parameters->get("background_url") != null) {
            $id_card_template->background = $bacground;
        }
        $id_card_template->save();

        return $id_card_template;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required',
            'html_template' => 'required',
            'editor_data' => 'required'
        ];
    }
}
