<?php

namespace App\BLoC\Web\ArcheryEvent;

use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Retrieval;

class FindArcheryEvent extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $archery_event = ArcheryEvent::find($parameters->get('id'));
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
        $archery_event_targets = $archery_event->archeryEventTargets;
        $scoring_system_categories = $archery_event->archeryEventScoringSystemCategories;
        foreach ($scoring_system_categories as $scoring_system_category) {
            $scoring_system_category->archeryEventScoringSystemDetails;
        }

        return $archery_event;
    }

    protected function validation($archery_event)
    {
        return [
            'id' => 'required|exists:archery_events,id',
        ];
    }
}