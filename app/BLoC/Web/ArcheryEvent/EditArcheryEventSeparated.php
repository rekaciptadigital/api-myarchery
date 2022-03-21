<?php

namespace App\BLoC\Web\ArcheryEvent;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventMoreInformation;
use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;
use App\Libraries\Upload;

class EditArcheryEventSeparated extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $archery_event = ArcheryEvent::find($parameters->get('id'));

        if (!empty($archery_event)) {
            return $this->processEdit($archery_event, $parameters, $admin);
        } else {
            throw new BLoCException("event_id not found");
        }
    }

    protected function validation($parameters)
    {
        return [
            "id" => "required",
        ];
    }

    private function processEdit($archery_event, $parameters, $admin)
    {
        $time = time();

        $archery_event->event_type = $parameters->get('event_type');
        $archery_event->event_competition = $parameters->get('event_competition');
        $archery_event->status = $parameters->get('status');
        if (!empty($parameters->get('event_banner'))) {
            $poster = Upload::setPath("asset/poster/")->setFileName("poster_" . $parameters->get('event_name'))->setBase64($parameters->get('event_banner'))->save();
            $archery_event->poster = $poster;
        }

        if ($parameters->get("handbook")) {
            $handbook = $parameters->get("handbook");
            if ($archery_event->handbook != $handbook) {
                $array_file_index_0 = explode(";", $handbook)[0];
                $ext_file_upload =  explode("/", $array_file_index_0)[1];
                if ($ext_file_upload != "pdf") {
                    throw new BLoCException("mohon inputkan tipe data pdf");
                }
                $handbook = Upload::setPath("asset/handbook/")->setFileName("handbook_" . $archery_event->event_name . "_" . $archery_event->id)->setBase64($handbook)->pdf();
                $archery_event->handbook = $handbook;
            }
        }

        $archery_event->event_name = $parameters->get('event_name');
        $archery_event->description = $parameters->get('event_description');
        $archery_event->location = $parameters->get('event_location');
        $archery_event->city_id = $parameters->get('event_city');
        $archery_event->location_type = $parameters->get('event_location_type');
        $archery_event->registration_start_datetime = $parameters->get('event_start_register');
        $archery_event->registration_end_datetime = $parameters->get('event_end_register');
        $archery_event->event_start_datetime = $parameters->get('event_start');
        $archery_event->event_end_datetime = $parameters->get('event_end');
        $archery_event->admin_id = $admin['id'];
        $archery_event->save();
    }
}
