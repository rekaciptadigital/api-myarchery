<?php

namespace App\BLoC\Web\ArcheryEvent;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategory;
use App\Models\ArcheryEventCategoryCompetition;
use App\Models\ArcheryEventCategoryCompetitionDistance;
use App\Models\ArcheryEventCategoryCompetitionTeam;
use App\Models\ArcheryEventQualification;
use App\Models\ArcheryEventQualificationDetail;
use App\Models\ArcheryEventRegistrationFee;
use App\Models\ArcheryEventRegistrationFeePerCategory;
use App\Models\ArcheryEventTarget;
use App\Models\ArcheryEventTeamCategory;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AddArcheryEvent extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $time = time();
        $event_slug = $time . '-' . Str::slug($parameters->get('event_name'));

        $poster = $parameters->get('poster');
        $handbook = $parameters->get('handbook');


        $archery_event = new ArcheryEvent();
        $archery_event->event_type = $parameters->get('event_type');
        $archery_event->poster = !is_null($poster) && $poster != '' && array_key_exists('base64', $poster) ? $this->saveBase64($poster['base64'], 'poster', $event_slug) : null;
        $archery_event->handbook = !is_null($handbook) && $handbook != '' && array_key_exists('base64', $handbook) ? $this->saveBase64($handbook['base64'], 'handbook', $event_slug) : null;
        $archery_event->event_name = $parameters->get('event_name');
        $archery_event->event_slug = $event_slug;
        $archery_event->registration_start_datetime = $parameters->get('registration_start_datetime');
        $archery_event->registration_end_datetime = $parameters->get('registration_end_datetime');
        $archery_event->event_start_datetime = $parameters->get('event_start_datetime');
        $archery_event->event_end_datetime = $parameters->get('event_end_datetime');
        $archery_event->location = $parameters->get('location');
        $archery_event->location_type = $parameters->get('location_type');
        $archery_event->description = $parameters->get('description');
        $archery_event->is_flat_registration_fee = $parameters->get('is_flat_registration_fee');
        $archery_event->published_datetime = $parameters->get('published_datetime');
        $archery_event->qualification_start_datetime = $parameters->get('qualification_start_datetime');
        $archery_event->qualification_end_datetime = $parameters->get('qualification_end_datetime');
        $archery_event->qualification_weekdays_only = $parameters->get('qualification_weekdays_only');
        $archery_event->qualification_session_length = $parameters->get('qualification_session_length') ? json_encode($parameters->get('qualification_session_length')) : null;
        $archery_event->admin_id = $admin['id'];
        $archery_event->save();

        if ($archery_event->event_type === 'marathon') {
            $qualification_days = $parameters->get('qualification_days');
            foreach ($qualification_days as $qualification_day) {
                $archery_event_qualification = new ArcheryEventQualification();
                $archery_event_qualification->event_id = $archery_event->id;
                $archery_event_qualification->day_id = $qualification_day['id'];
                $archery_event_qualification->day_label = $qualification_day['label'];
                $archery_event_qualification->save();

                $details = $qualification_day['details'];
                foreach ($details as $detail) {
                    $archery_event_qualification_detail = new ArcheryEventQualificationDetail();
                    $archery_event_qualification_detail->event_qualification_id = $archery_event_qualification->id;
                    $archery_event_qualification_detail->start_time = $detail['start_time'];
                    $archery_event_qualification_detail->end_time = $detail['end_time'];
                    $archery_event_qualification_detail->quota = $detail['quota'];
                    $archery_event_qualification_detail->save();
                }
            }
        }

        $targets = $parameters->get('targets');
        foreach ($targets as $target) {
            $archery_event_target = new ArcheryEventTarget();
            $archery_event_target->event_id = $archery_event->id;
            $archery_event_target->target_id = $target['id'];
            $archery_event_target->save();
        }

        $team_categories = $parameters->get('team_categories', []);
        foreach ($team_categories as $team_category) {
            $archery_event_team_category = new ArcheryEventTeamCategory();
            $archery_event_team_category->event_id = $archery_event->id;
            $archery_event_team_category->team_category_id = $team_category['id'];
            $archery_event_team_category->save();
        }

        $event_categories = $parameters->get('event_categories', []);
        foreach ($event_categories as $event_category) {
            $archery_event_category = new ArcheryEventCategory();
            $archery_event_category->event_id = $archery_event->id;
            $archery_event_category->age_category_id = $event_category['age_category']['id'];
            $archery_event_category->max_date_of_birth = $event_category['max_date_of_birth'];
            $archery_event_category->save();

            $competition_categories = $event_category['competition_categories'];
            foreach ($competition_categories as $competition_category) {
                $archery_event_category_competition = new ArcheryEventCategoryCompetition();
                $archery_event_category_competition->event_category_id = $archery_event_category->id;
                $archery_event_category_competition->competition_category_id = $competition_category['competition_category']['id'];
                $archery_event_category_competition->save();

                $team_categories = $competition_category['team_categories'];
                foreach ($team_categories as $team_category) {
                    $archery_event_category_competition_team = new ArcheryEventCategoryCompetitionTeam();
                    $archery_event_category_competition_team->event_category_competition_id = $archery_event_category_competition->id;
                    $archery_event_category_competition_team->team_category_id = $team_category['id'];
                    $archery_event_category_competition_team->quota = $team_category['quota'];
                    $archery_event_category_competition_team->save();
                }

                $distances = $competition_category['distances'];
                foreach ($distances as $distance) {
                    $archery_event_category_competition_distance = new ArcheryEventCategoryCompetitionDistance();
                    $archery_event_category_competition_distance->event_category_competition_id = $archery_event_category_competition->id;
                    $archery_event_category_competition_distance->distance_id = $distance['id'];
                    $archery_event_category_competition_distance->save();
                }
            }
        }

        $registration_fees = $parameters->get("registration_fees", []);
        foreach ($registration_fees as $registration_fee) {
            $archery_event_registration_fee = new ArcheryEventRegistrationFee();
            $archery_event_registration_fee->event_id = $archery_event->id;
            $archery_event_registration_fee->registration_type_id = $registration_fee['id'];
            $archery_event_registration_fee->price = $registration_fee['price'];
            $archery_event_registration_fee->start_date = array_key_exists('start_date', $registration_fee) ? $registration_fee['start_date'] : null;
            $archery_event_registration_fee->end_date = array_key_exists('end_date', $registration_fee) ? $registration_fee['end_date'] : null;
            $archery_event_registration_fee->save();

            $category_prices = $registration_fee['category_prices'];
            foreach ($category_prices as $category_price) {
                $archery_event_registration_fee_per_category = new ArcheryEventRegistrationFeePerCategory();
                $archery_event_registration_fee_per_category->event_registration_fee_id = $archery_event_registration_fee->id;
                $archery_event_registration_fee_per_category->team_category_id = $category_price['id'];
                $archery_event_registration_fee_per_category->price = $archery_event->is_flat_registration_fee ? $registration_fee['price'] : $category_price['price'];
                $archery_event_registration_fee_per_category->save();
            }
        }

        return $archery_event;
    }

    protected function validation($parameters)
    {
        return [
            "event_type" => "required",
            "event_name" => "required",
            "registration_start_datetime" => "required",
            "registration_end_datetime" => "required",
            "event_start_datetime" => "required",
            "event_end_datetime" => "required",
            "location" => "required",
            "location_type" => "required",
            "is_flat_registration_fee" => "required",
            "registration_fees" => "required",
            "targets" => "required",
            "team_categories" => "required",
            "event_categories" => "required",
            "qualification_days" => "required_if:event_type,marathon"
        ];
    }
}
