<?php

namespace App\BLoC\Web\ArcheryCategoryDetail;

use App\Models\ArcheryEventCategoryDetail;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;

class AddArcheryCategoryDetail extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $archery_category_detail = new ArcheryEventCategoryDetail();
        $archery_category_detail->event_id = $parameters->get('event_id');
        $archery_category_detail->age_category_id = $parameters->get('age_category_id');
        $archery_category_detail->competition_category_id = $parameters->get('competition_category_id');
        $archery_category_detail->distance_id  = $parameters->get('distance_id');
        $archery_category_detail->team_category_id  = $parameters->get('team_category_id');
        $archery_category_detail->quota = $parameters->get('quota');
        $archery_category_detail->fee = $parameters->get('fee');
        $archery_category_detail->save();

        return $archery_category_detail;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required',
            'age_category_id' => 'required',
            'competition_category_id' => 'required',
            'distance_id' => 'required',
            'team_category_id' => 'required',
            'quota' => 'required',
            'fee' => 'required',

        ];
    }
}
