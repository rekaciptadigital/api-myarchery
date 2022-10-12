<?php

namespace App\BLoC\Web\ArcheryEvent;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventMoreInformation;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;
use App\Libraries\Upload;
use App\Models\City;

class UpdateArcheryEventV2 extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();

        $event = ArcheryEvent::find($parameters->get("event_id"));
        if (!$event) {
            throw new BLoCException("Event tidak ditemukan");
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException("Forbiden");
        }

        $time = time();

        // Upload Poster
        if ($parameters->get("event_banner")) {
            $poster = $parameters->get("event_banner");
            if ($event->poster != $poster) {
                $array_file_index_0 = explode(";", $poster)[0];
                $ext_file_upload =  explode("/", $array_file_index_0)[1];
                if ($ext_file_upload != "png" && $ext_file_upload != "jpg" && $ext_file_upload != "jpeg") {
                    throw new BLoCException("mohon inputkan tipe data gambar");
                }
                $poster = Upload::setPath("asset/poster/")->setFileName("poster_" . $parameters->get("event_name"))->setBase64($parameters->get('event_banner'))->save();
                $event->poster = $poster;
            }
        }

        // upload handbook
        if ($parameters->get("handbook")) {
            $handbook = $parameters->get("handbook");
            if ($event->handbook != $handbook) {
                $array_file_index_0 = explode(";", $handbook)[0];
                $ext_file_upload =  explode("/", $array_file_index_0)[1];
                if ($ext_file_upload != "pdf") {
                    throw new BLoCException("mohon inputkan tipe data pdf");
                }
                $handbook = Upload::setPath("asset/handbook/")->setFileName("handbook_" . $event->event_name . "_" . $event->id)->setBase64($handbook)->pdf();
                $event->handbook = $handbook;
            }
        }

        $city = City::find($parameters->get("event_city"));
        if (!$city) {
            throw new BLoCException("kota tidak tersedia");
        }

        $event->event_name = $parameters->get('event_name');
        $event->description = $parameters->get("event_description");
        $event->location = $parameters->get("event_location");
        $event->city_id = $city->id;
        $event->location_type = $parameters->get("event_location_type");
        $event->registration_start_datetime = $parameters->get("event_start_register");
        $event->registration_end_datetime = $parameters->get("event_end_register");
        $event->event_start_datetime = $parameters->get("event_start");
        $event->event_end_datetime = $parameters->get("event_end");
        $event->is_private = $parameters->get('is_private') ?? false;
        $event->admin_id = $admin->id;
        $event->save();

        return ArcheryEvent::detailEventById($event->id);
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required",
            "event_type" => "in:Full_day,Marathon",
            "event_competition" => "in:Tournament,Games,Selection",
            // "status" => "integer|in:1,0",
            "event_banner" => "string",
            "event_name" => "required|string",
            "event_location" => "string",
            "event_city" => "integer",
            "event_location_type" => "in:Indoor,Outdoor,Both",
            "event_end" => "after:event_start",
            "more_information" => "array"
        ];
    }
}
