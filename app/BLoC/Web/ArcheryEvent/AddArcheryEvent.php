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

class AddArcheryEvent extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $event_type=$parameters->get('event_type');
     
        if ($event_type === 'Full_day') {
            $time = time();
            
            $archery_event = new ArcheryEvent();

            $archery_event->event_type = $event_type;
            $archery_event->event_competition = $parameters->get('event_competition'); 
            $archery_event->status = $parameters->get('status'); 

            $public_informations = $parameters->get('public_information');
            
            $poster = Upload::setPath("asset/poster/")->setFileName("poster_".$public_informations['event_name'])->setBase64($public_informations['event_banner'])->save();
            $archery_event->poster = $poster;
            $archery_event->event_name = $public_informations['event_name'];
            $archery_event->description = $public_informations['event_description'];
            $archery_event->location = $public_informations['event_location']; 
            $archery_event->city_id = $public_informations['event_city'];
            $archery_event->location_type =$public_informations['event_location_type']; 
            $archery_event->registration_start_datetime =$public_informations['event_start_register']; 
            $archery_event->registration_end_datetime = $public_informations['event_end_register'];
            $archery_event->event_start_datetime = $public_informations['event_start'];
            $archery_event->event_end_datetime = $public_informations['event_end'];
            $archery_event->event_slug = $time . '-' . Str::slug($public_informations['event_name']);
            $archery_event->admin_id = $admin['id'];
            $archery_event->save();

            $more_informations = $parameters->get('more_information', []);
            foreach ($more_informations as $more_information) {
                $archery_event_more_information = new ArcheryEventMoreInformation();
                $archery_event_more_information->event_id = $archery_event->id;
                $archery_event_more_information->title = $more_information['title'];
                $archery_event_more_information->description = $more_information['description'];
                $archery_event_more_information->save();
            }

            $event_categories = $parameters->get('event_categories', []);
            foreach ($event_categories as $event_category) {
                $archery_event_category_detail = new ArcheryEventCategoryDetail();
                $archery_event_category_detail->event_id = $archery_event->id;
                $archery_event_category_detail->age_category_id = $event_category['age_category_id'];
                $archery_event_category_detail->competition_category_id = $event_category['competition_category_id'];
                $archery_event_category_detail->distance_id = $event_category['distance_id'];
                $archery_event_category_detail->team_category_id = $event_category['team_category_id'];
                $archery_event_category_detail->quota = $event_category['quota'];
                $archery_event_category_detail->fee = $event_category['fee'];
                $archery_event_category_detail->save();
            }

            return $archery_event;
        }else{
            throw new BLoCException("masukan event_type sebagai Full_day");
        }

    }

    protected function validation($parameters)
    {
        return [
            "event_type" => "required",
            "event_competition" => "required",
            "status" => "required",
            "public_information" => "required|array|min:1",
            "public_information.event_banner" => "required",
            "public_information.event_name" => "required",
            "public_information.event_description" => "required",
            "public_information.event_location" => "required",
            "public_information.event_city" => "required",
            "public_information.event_location_type" => "required",
            "public_information.event_start_register" => "required",
            "public_information.event_end_register" => "required",
            "public_information.event_start" => "required",
            "public_information.event_end" => "required",
            "event_categories" => "required|array|min:1",

        ];
    }
}
