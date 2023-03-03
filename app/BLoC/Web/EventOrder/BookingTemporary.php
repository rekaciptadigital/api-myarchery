<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use App\Models\ClassificationEventRegisters;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DAI\Utils\Exceptions\BLoCException;

class BookingTemporary extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $category_id = $parameters->get("category_id");
        $user = Auth::guard('app-api')->user();
        $category = ArcheryEventCategoryDetail::find($category_id);
        $classificationChildren = $parameters->get('classificationChildren');
        $classificationCountryId = $parameters->get('classificationCountryId');
        $classificationProvinceId = $parameters->get('classificationProvinceId');
        $classificationCityId = $parameters->get('classificationCityId');
        $classificationArcheryClub = $parameters->get('classificationArcheryClub');

        if (!$category) {
            throw new BLoCException("category not found");
        }
        $event = ArcheryEvent::find($category['event_id']);

        $data_classification_event_register = [
            'event_id' => $category['event_id'],
            'user_id' => $user->id,
            'archery_club_id' => 0,
            'country_id' => 0,
            'provinsi_id' => 0,
            'city_id' => 0,
            'children_classification' => 0
        ];


        if ($event['with_contingent'] == 1) {
            if ($event['detail_parent_classification']['id'] == 1) {
                if (empty($classificationArcheryClub)) {
                    throw new BLoCException("classification from archery club is required!");
                } else {
                    $data_classification_event_register['archery_club_id'] = $classificationArcheryClub;
                }
            } elseif ($event['detail_parent_classification']['id'] == 2) {
                if (empty($classificationCountryId)) {
                    throw new BLoCException("classification country is required!");
                } else {
                    $data_classification_event_register['country_id'] = $classificationCountryId;
                }
            } elseif ($event['detail_parent_classification']['id'] == 3) {
                if (empty($classificationProvinceId)) {
                    throw new BLoCException("classification province is required!");
                } else {
                    $data_classification_event_register['country_id'] = $event['detail_country_classification']['id'];
                    $data_classification_event_register['provinsi_id'] = $classificationProvinceId;
                }
            } elseif ($event['detail_parent_classification']['id'] == 4) {
                if (empty($classificationCityId)) {
                    throw new BLoCException("classification city is required!");
                } else {
                    $data_classification_event_register['country_id'] = $event['detail_country_classification']['id'];
                    $data_classification_event_register['provinsi_id'] = $event['detail_province_classification']['id'];
                    $data_classification_event_register['city_id'] = $classificationCityId;
                }
            } else {
                if (empty($classificationChildren)) {
                    throw new BLoCException("classification children is required!");
                } else {
                    $data_classification_event_register['children_classification'] = $classificationChildren;
                }
            }
        }

        $participant = ArcheryEventParticipant::insertParticipant($user, Str::uuid(), $category, 6, 0, null, strtotime(env("EXPIRED_BOOKING_TIME", "+15 minutes"), time()), 0, $data_classification_event_register['city_id'], $data_classification_event_register['country_id'], $data_classification_event_register['provinsi_id'], $data_classification_event_register['children_classification']);


        $contingent_classification = ClassificationEventRegisters::create($data_classification_event_register);
        $contingent_classification->makeHidden(['created_at', 'deleted_at']);

        return [
            "participant_id" => $participant->id,
            "category_id" => $category_id,
            "expired_booking_time" => $participant->expired_booking_time,
            "contingent_classification" => $contingent_classification
        ];
    }

    protected function validation($parameters)
    {
        return [
            'category_id' => 'required',
        ];
    }
}
