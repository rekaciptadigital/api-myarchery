<?php

namespace App\BLoC\Web\ArcheryEvent;

use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Transactional;

class EditArcheryEvent extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_event = ArcheryEvent::find($parameters->get('id'));
        $archery_event->save();

        return $archery_event;
    }

    protected function validation($parameters)
    {
        return [
            'id' => [
                'required',
                'exists:archery_events,id',
            ],
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
