<?php

namespace App\BLoC\General\Event;

use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;

class GetDetailEventByIdGeneral extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get("event_id");
        $event = ArcheryEvent::find($event_id);

        if (!$event) {
            throw new BLoCException("event tidak ditemukan");
        }

        $response = [];

        if ($event) {
            $response["id"] = $event->id;
            $response["poster"] = $event->poster;
            $response["handbook"] = $event->handbook;
            $response["event_name"] = $event->event_name;
            $response["registration_start_datetime"] = $event->registration_start_datetime;
            $response["registration_end_datetime"] = $event->registration_end_datetime;
            $response["event_start_datetime"] = $event->event_start_datetime;
            $response["event_end_datetime"] = $event->event_end_datetime;
            $response["location"] = $event->location;
            $response["location_type"] = $event->location_type;
            $response["description"] = $event->description;
            $response["admin_id"] = $event->admin_id;
            $response["event_slug"] = $event->event_slug;
            $response["event_competition"] = $event->event_competition;
            $response["city_id"] = $event->city_id;
            $response["status"] = $event->status;
            $response["event_type"] = $event->event_type;
            $response["need_verify"] = $event->need_verify;
            $response["detail_admin"] = $event->detail_admin;
            $response["detail_city"] = $event->detail_city;
            $response["event_status"] = $event->event_status;
            $response["more_information"] = $event->more_information;
            $response["event_price"] = $event->event_price;
        }

        return $response;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required",
        ];
    }
}
