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

class CreateArcheryEventV2 extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $event_type = $parameters->get('event_type');

        if ($event_type === 'Full_day' || $event_type === "Marathon") {
            $time = time();

            $archery_event = new ArcheryEvent();
            $archery_event->event_type = $event_type;
            $archery_event->event_competition = $parameters->get('event_competition');
            $archery_event->status = 0;
            $archery_event->is_private = $parameters->get('is_private') ?? false;

            // Upload Poster
            if ($parameters->get("event_banner")) {
                $array_file_index_0 = explode(";", $parameters->get("event_banner"))[0];
                $ext_file_upload =  explode("/", $array_file_index_0)[1];
                if ($ext_file_upload != "png" && $ext_file_upload != "jpg" && $ext_file_upload != "jpeg") {
                    throw new BLoCException("mohon inputkan tipe data gambar");
                }
                $poster = Upload::setPath("asset/poster/")->setFileName("poster_" . $parameters->get("event_name"))->setBase64($parameters->get('event_banner'))->save();
                $archery_event->poster = $poster;
            }

            // upload handbook
            if ($parameters->get("handbook")) {
                $array_file_index_0 = explode(";", $parameters->get("handbook"))[0];
                $ext_file_upload =  explode("/", $array_file_index_0)[1];
                if ($ext_file_upload != "pdf") {
                    throw new BLoCException("mohon inputkan tipe data pdf");
                }
                $handbook = Upload::setPath("asset/handbook/")->setFileName("handbook_" . $parameters->get("event_name"))->setBase64($parameters->get("handbook"))->pdf();
                $archery_event->handbook = $handbook;
            }

            $city = City::find($parameters->get("event_city"));
            if (!$city) {
                throw new BLoCException("kota tidak tersedia");
            }

            $archery_event->event_name = $parameters->get('event_name');
            $archery_event->description = $parameters->get("event_description");
            $archery_event->location = $parameters->get("event_location");
            $archery_event->city_id = $city->id;
            $archery_event->location_type = $parameters->get("event_location_type");

            $slug = Str::slug($parameters->get("event_name"));

            $check_slug = ArcheryEvent::where("event_slug", $slug)->first();
            if ($check_slug) {
                $slug = $time . '-' . $slug;
            }

            $archery_event->event_slug = $slug;
            $archery_event->admin_id = $admin->id;
            $archery_event->save();

            $more_informations = $parameters->get('more_information', []);
            if (count($more_informations) > 0) {
                foreach ($more_informations as $more_information) {
                    $archery_event_more_information = new ArcheryEventMoreInformation();
                    $archery_event_more_information->event_id = $archery_event->id;
                    $archery_event_more_information->title = $more_information['title'];
                    $archery_event_more_information->description = $more_information['description'];
                    $archery_event_more_information->save();
                }
            }

            return ArcheryEvent::detailEventById($archery_event->id);
        } else {
            throw new BLoCException("untuk saat ini yang dibuka hanya full day dan marathon");
        }
    }

    protected function validation($parameters)
    {
        return [
            "event_type" => "required|in:Full_day,Marathon",
            "event_competition" => "required|in:Tournament,Games,Selection",
            // "status" => "required|integer|in:1,0",
            "event_banner" => "required",
            "event_name" => "required",
            "event_location" => "required",
            "event_city" => "required",
            "event_location_type" => "required|in:Indoor,Outdoor,Both",
            "more_information" => "array"
        ];
    }
}
