<?php

namespace App\BLoC\Web\ArcheryEvent;

use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Transactional;

class AddArcheryEvent extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_event = new ArcheryEvent();
        $archery_event->poster = $parameters->get('poster');
        $archery_event->technical_handbook = $parameters->get('technical_handbook');
        $archery_event->name = $parameters->get('name');
        $archery_event->registration_start_date = $parameters->get('registration_start_date');
        $archery_event->registration_end_date = $parameters->get('registration_end_date');
        $archery_event->execution_start_date = $parameters->get('execution_start_date');
        $archery_event->execution_end_date = $parameters->get('execution_end_date');
        $archery_event->phone_number = $parameters->get('phone_number');
        $archery_event->location = $parameters->get('location');
        $archery_event->location_type = $parameters->get('location_type');
        $archery_event->individual_registration_fee = $parameters->get('individual_registration_fee');
        $archery_event->group_registration_fee = $parameters->get('group_registration_fee');
        $archery_event->total_price = $parameters->get('total_price');
        $archery_event->total_price_currency = $parameters->get('total_price_currency');
        $archery_event->description = $parameters->get('description');
        $archery_event->is_public = $parameters->get('is_public');
        $archery_event->published_datetime = $parameters->get('published_datetime');
        $archery_event->admin_id = $parameters->get('admin_id');
        $archery_event->save();

        return $archery_event;
    }

    protected function validation($parameters)
    {
        return [
            'poster' => ['required'],
            'name' => ['required'],
            'registration_start_date' => ['required'],
            'registration_end_date' => ['required'],
            'execution_start_date' => ['required'],
            'execution_end_date' => ['required'],
            'phone_number' => ['required'],
            'location' => ['required'],
            'location_type' => ['required'],
            'individual_registration_fee' => ['required'],
            'group_registration_fee' => ['required'],
            'total_price' => ['required'],
            'total_price_currency' => ['required'],
            'description' => ['required'],
            'is_public' => ['required'],
            'published_datetime' => ['required'],
            'admin_id' => ['required'],
            'event_categories' => ['required', 'array'],
            'event_categories.*' => ['required'],
            'event_categories.*.archery_age_category_id' => ['required'],
            'event_categories.*.max_date_of_birth' => ['required'],
            'event_categories.*.archery_category_id' => ['required'],
            'event_categories.*.distance' => ['required'],
            'event_categories.*.quota' => ['required'],
            'event_categories.*.category_type' => ['required'],
        ];
    }
}
