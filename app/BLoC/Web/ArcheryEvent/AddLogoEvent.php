<?php

namespace App\BLoC\Web\ArcheryEvent;

use App\Libraries\Upload;
use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;

class AddLogoEvent extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $logo = $parameters->get("logo");
        $event_id = $parameters->get("event_id");
        $event = ArcheryEvent::find($event_id);

        $array_file_index_0 = explode(";", $logo)[0];
        $ext_file_upload =  explode("/", $array_file_index_0)[1];

        if ($ext_file_upload != "jpg" && $ext_file_upload != "jpeg" && $ext_file_upload != "png") {
            throw new BLoCException("mohon inputkan tipe data gambar png, jpeg, jpg");
        }

        $logo = Upload::setPath("asset/logo_event/")->setFileName("logo_event_" . $event_id)->setBase64($logo)->save();

        $event->logo = $logo;
        $event->save();
        return $event->logo;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required|exists:archery_events,id',
            "logo" => "required|string"
        ];
    }
}
