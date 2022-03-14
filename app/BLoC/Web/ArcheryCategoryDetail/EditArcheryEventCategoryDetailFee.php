<?php

namespace App\BLoC\Web\ArcheryCategoryDetail;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;

class EditArcheryEventCategoryDetailFee extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $archery_event_category_detail_fee = ArcheryEventCategoryDetail::where('event_id', $parameters->get('event_id'));

        if (!empty($archery_event_category_detail_fee)) {
            return $this->processEdit($parameters);
        } else {
            throw new BLoCException("event_id not found");
        }
    }

    protected function validation($parameters)
    {
        return [
            "data" => "required|array|min:1",
            "event_id" => "required"
        ];
    }

    private function processEdit($parameters)
    {
        $datas = $parameters->get('data', []);
        foreach ($datas as $data) {
            $archery_event_category_detail_fee = ArcheryEventCategoryDetail::where('event_id', $parameters->get('event_id'))
                ->where('team_category_id', $data['team_category_id']);
            if (!empty($archery_event_category_detail_fee)) {
                $values = ArcheryEventCategoryDetail::where('event_id', $parameters->get('event_id'))
                    ->where('team_category_id', $data['team_category_id'])
                    ->update([
                        'fee' => $data['fee'],
                        'early_bird' => $data['early_bird'],
                        'end_date_early_bird' => $data['end_date_early_bird']
                    ]);
            } else {
                throw new BLoCException("team_category_id not found");
            }
        }
    }
}
