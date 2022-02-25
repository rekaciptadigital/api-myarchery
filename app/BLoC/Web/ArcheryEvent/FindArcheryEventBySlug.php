<?php

namespace App\BLoC\Web\ArcheryEvent;

use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Helpers\BLoC;

class FindArcheryEventBySlug extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_event = ArcheryEvent::where('event_slug', $parameters->get('slug'))->first();
        $archery_event_categories = $archery_event->archeryEventCategories;
        foreach ($archery_event_categories as $archery_event_category) {
            $archery_event_category_competitions = $archery_event_category->archeryEventCategoryCompetitions;
            foreach ($archery_event_category_competitions as $archery_event_category_competition) {
                $archery_event_category_competition->archeryEventCategoryCompetitionTeams;
            }
        }
        $archery_event_qualifications = $archery_event->archeryEventQualifications;
        foreach ($archery_event_qualifications as $archery_event_qualification) {
            $archery_event_qualification->archeryEventQualificationDetails;
        }
        $archery_event_registration_fees = $archery_event->archeryEventRegistrationFees;
        foreach ($archery_event_registration_fees as $archery_event_registration_fee) {
            $archery_event_registration_fee->archeryEventRegistrationFeePerCategory;
        }
        $archery_event->archeryEventTargets;

        $flat_categories = $archery_event->flat_categories;

        $new_flat_categories = [];
        foreach ($flat_categories as $flat_category) {
            if($flat_category->for_age > 0){
                $curYear = date('Y'); 
                $flat_category->minBirthDay =  ($curYear - $flat_category->for_age)."-01-01";        
                $flat_category->maxBirthDay =  ($curYear - $flat_category->for_age)."-12-31";   
            }
            $flat_category->price = BLoC::call('getEventPrice', [
                'event_id' => $archery_event['id'],
                'category_event' => collect($flat_category)->all()
            ]);
            $new_flat_categories[] = $flat_category;
        }
        $archery_event = collect($archery_event)->all();
        $archery_event['flat_categories'] = $new_flat_categories;

        $categories = ArcheryEvent::getCategories($archery_event["id"]);

        $archery_event["categories"] = $categories;
        return $archery_event;
    }

    protected function validation($archery_event)
    {
        return [
            'slug' => 'required|exists:archery_events,event_slug',
        ];
    }
}
