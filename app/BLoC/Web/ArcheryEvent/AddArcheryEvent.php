<?php

namespace App\BLoC\Web\ArcheryEvent;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategory;
use App\Models\ArcheryEventCategoryCompetition;
use App\Models\ArcheryEventCategoryTeam;
use App\Models\ArcheryEventRegistrationFee;
use App\Models\ArcheryEventRegistrationFeePerCategory;
use App\Models\ArcheryEventStep;
use App\Models\ArcheryEventTarget;
use App\Models\ArcheryEventTeamCategory;
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
        $archery_event->event_type = $parameters->get('event_type');
        $archery_event->poster = $parameters->get('poster');
        $archery_event->handbook = $parameters->get('handbook');
        $archery_event->event_name = $parameters->get('event_name');
        $archery_event->registration_start_datetime = $parameters->get('registration_start_datetime');
        $archery_event->registration_end_datetime = $parameters->get('registration_end_datetime');
        $archery_event->event_start_datetime = $parameters->get('event_start_datetime');
        $archery_event->event_end_datetime = $parameters->get('event_end_datetime');
        $archery_event->location = $parameters->get('location');
        $archery_event->location_type = $parameters->get('location_type');
        $archery_event->description = $parameters->get('description');
        $archery_event->is_flat_registration_fee = $parameters->get('is_flat_registration_fee');
        $archery_event->published_datetime = $parameters->get('published_datetime');
        $archery_event->save();

        if ($archery_event->event_type === 'marathon') {
            $archery_event_step = new ArcheryEventStep();
            $archery_event_step->event_id = $archery_event->id;
            $archery_event_step->qualification_start_datetime = $parameters->get('qualification_start_datetime');
            $archery_event_step->qualification_end_datetime = $parameters->get('qualification_end_datetime');
            $archery_event_step->qualification_session_per_day = $parameters->get('qualification_session_per_day');
            $archery_event_step->qualification_quota_per_day = $parameters->get('qualification_quota_per_day');
            $archery_event_step->elimination_start_datetime = $parameters->get('elimination_start_datetime');
            $archery_event_step->elimination_end_datetime = $parameters->get('elimination_end_datetime');
            $archery_event_step->elimination_session_per_day = $parameters->get('elimination_session_per_day');
            $archery_event_step->elimination_quota_per_day = $parameters->get('elimination_quota_per_day');
        }

        $targets = $parameters->get('targets');
        foreach ($targets as $target) {
            $archery_event_target = new ArcheryEventTarget();
            $archery_event_target->event_id = $archery_event->id;
            $archery_event_target->target_id = $target['id'];
            $archery_event_target->target_label = $target['label'];
            $archery_event_target->save();
        }

        $team_categories = $parameters->get('team_categories', []);
        foreach ($team_categories as $team_category) {
            $archery_event_team_category = new ArcheryEventTeamCategory();
            $archery_event_team_category->event_id = $archery_event->id;
            $archery_event_team_category->team_category_id = $team_category['id'];
            $archery_event_team_category->team_category_label = $team_category['label'];
            $archery_event_team_category->save();
        }

        $event_categories = $parameters->get('event_categories', []);
        foreach ($event_categories as $event_category) {
            $archery_event_category = new ArcheryEventCategory();
            $archery_event_category->event_id = $archery_event->id;
            $archery_event_category->age_category_id = $event_category['age_category']['id'];
            $archery_event_category->age_category_label = $event_category['age_category']['label'];
            $archery_event_category->max_date_of_birth = $event_category['max_date_of_birth'];
            $archery_event_category->save();

            $competition_categories = $event_category['competition_categories'];
            foreach ($competition_categories as $competition_category) {
                $archery_event_category_competition = new ArcheryEventCategoryCompetition();
                $archery_event_category_competition->event_category_id = $archery_event_category->id;
                $archery_event_category_competition->competition_category_id = $competition_category['competition_category']['id'];
                $archery_event_category_competition->competition_category_label = $competition_category['competition_category']['label'];
                $archery_event_category_competition->distances = implode(",", $competition_category['distances']);
                $archery_event_category_competition->save();
            }

            $team_categories = $event_category['team_categories'];
            foreach ($team_categories as $team_category) {
                $archery_event_category_team = new ArcheryEventCategoryTeam();
                $archery_event_category_team->event_category_id = $archery_event_category->id;
                $archery_event_category_team->team_category_id = $team_category['id'];
                $archery_event_category_team->team_category_label = $team_category['label'];
                $archery_event_category_team->quota = $team_category['quota'];
                $archery_event_category_team->save();
            }
        }

        $registration_fees = $parameters->get("registration_fees", []);
        foreach ($registration_fees as $registration_fee) {
            $archery_event_registration_fee = new ArcheryEventRegistrationFee();
            $archery_event_registration_fee->event_id = $archery_event->id;
            $archery_event_registration_fee->registration_type = $registration_fee['registration_type'];
            $archery_event_registration_fee->price = $registration_fee['price'];
            $archery_event_registration_fee->save();

            $category_prices = $registration_fee['category_prices'];
            foreach ($category_prices as $category_price) {
                $archery_event_registration_fee_per_category = new ArcheryEventRegistrationFeePerCategory();
                $archery_event_registration_fee_per_category->event_registration_fee_id = $archery_event_registration_fee->id;
                $archery_event_registration_fee_per_category->team_category = $category_price['team_category'];
                $archery_event_registration_fee_per_category->price = $category_price['price'];
                $archery_event_registration_fee_per_category->save();
            }
        }

        return [];
    }

    protected function validation($parameters)
    {
        return [];
    }
}
