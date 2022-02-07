<?php

namespace App\BLoC\App\ArcheryEvent;

use App\Models\Admin;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventMoreInformation;
use App\Models\City;
use App\Models\Provinces;
use Illuminate\Support\Str;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetDetailEventById extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get('event_id');
        $data = ArcheryEvent::find($event_id);
        if (!$data) {
            throw new BLoCException("event not found");
        }
        
        $event_url = env('WEB_DOMAIN', 'https://my-archery.id') . '/event/' . Str::slug($data->admin_name) . '/' . $data->event_slug;

        $admins = Admin::where('id', $data->admin_id)->get();
        $admins_data = [];
        if ($admins) {
            foreach ($admins as $key => $value) {
                $admins_data = [
                    'id' => $value->id,
                    'name' => $value->name,
                    'email' => $value->email,
                    'avatar' => $value->avatar,
                ];
            }
        }

        $more_informations = ArcheryEventMoreInformation::where('event_id', $data->id)->get();
        $moreinformations_data = [];
        if ($more_informations) {
            foreach ($more_informations as $key => $value) {
                $moreinformations_data[] = [
                    'id' => $value->id,
                    'event_id' => $value->event_id,
                    'title' => $value->title,
                    'description' => $value->description,
                ];
            }
        }

        $city = City::find($data->city_id);
        $output = [
            "id" => $data->id,
            "event_type" => $data->event_type,
            "event_competition" => $data->event_competition,
            "public_information" => [
                'event_name' => $data->event_name,
                'event_banner' => $data->poster,
                'event_description' => $data->description,
                'event_location' => $data->location,
                'event_city' => [
                    'city_id' => $city ? $city->id : null,
                    'name_city' => $city ? $city->name : null,
                    'province_id' => $city ? Provinces::find($city->province_id)->id : null,
                    'province_name' => $city ? Provinces::find($city->province_id)->name : null
                ],
                'event_location_type' => $data->location_type,
                'event_start_register' => $data->registration_start_datetime,
                'event_end_register' => $data->registration_end_datetime,
                'event_start' => $data->event_start_datetime,
                'event_end' => $data->event_end_datetime,
                'event_status' => $data->status,
                'event_slug' => $data->event_slug,
                'event_url' => $event_url
            ],
            'more_information' => $moreinformations_data,
            'admins' => $admins_data

        ];

        unset($moreinformations_data);
        unset($eventcategories_data);


        return $output;
    }

    protected function validation($parameters)
    {
        return [
            'page' => 'min:1',
            'limit' => 'min:1'
        ];
    }
}
