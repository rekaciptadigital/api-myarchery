<?php

namespace App\BLoC\Web\BudRest;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\BudRest;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class SetBudRest extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $event = ArcheryEvent::find($parameters->get('event_id'));
        if (!$event) {
            throw new BLoCException('event not found');
        }

        if ($event->admin_id != $admin->id) {
            throw new BLoCException('you are not owner this event');
        }

        $data = $parameters->all();

        $data_insert = [];

        foreach ($data['event_category'] as $key) {
            if ($key['bud_rest_start'] >= $key['bud_rest_end']) {
                throw new BLoCException("input not valid");
            }
            $category_detail = ArcheryEventCategoryDetail::where('id', $key['archery_event_category_id'])->where('event_id', $event->id)->first();

            if (!$category_detail) {
                throw new BLoCException('category_detail_not found');
            }
            if ($category_detail->category_team != 'Individual') {
                throw new BLoCException("team must be individual type");
            }

            if (count($data_insert) == 0) {
                array_push($data_insert, $key);
            } else {
                foreach ($data_insert as $b) {
                    if ($key['bud_rest_start'] >= $b['bud_rest_start'] && $key['bud_rest_start'] <= $b['bud_rest_end']) {
                        throw new BLoCException('format not valid');
                    }
                }
                array_push($data_insert, $key);
            }
        }

        foreach($data_insert as $di){
            BudRest::create($di);
        }

        $return = [];
        $return['event'] = $event;
        $archery_category_detail = ArcheryEventCategoryDetail::where('event_id', $event->id)->get();
        $archery_category_detail_individual = $archery_category_detail->where('category_team', 'Individual');
        foreach ($archery_category_detail_individual as $archery) {
            $category['archery_category_detail'] = $archery;
            $bud = BudRest::where('archery_event_category_id', $archery->id)->get();
            $category['bud_rest'] = $bud;
            array_push($return, $category);
        }

        return $return;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required|integer',
            'event_category' => 'required|array',
            'event_category.*.archery_event_category_id' => 'required|integer|unique:bud_rest',
            'event_category.*.bud_rest_start' => 'required|integer|min:1',
            'event_category.*.bud_rest_end' => 'required|integer|min:1',
            'event_category.*.target_face' => 'required|integer|min:1',
            'event_category.*.type' => 'required|in:qualification,elimination'
        ];
    }
}
