<?php

namespace App\BLoC\Web\BudRest;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\BudRest;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;

class GetBudRest extends Transactional
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

        $archery_category_detail = ArcheryEventCategoryDetail::where('event_id', $event->id)->get();

        $data = [];
        $data['event'] = $event;

        $data['category_detail'] = [];
        foreach($archery_category_detail as $detail){
            if($detail->category_team == 'Individual'){
                $detail['bud_rest'] = BudRest::where('archery_event_category_id', $detail->id)->get();
                array_push($data['category_detail'], $detail);
            }
        }

        return $data;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required|integer',
        ];
    }
}
