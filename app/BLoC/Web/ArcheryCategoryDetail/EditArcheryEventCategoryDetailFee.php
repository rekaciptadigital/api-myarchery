<?php

namespace App\BLoC\Web\ArcheryCategoryDetail;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipant;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Carbon;

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
            $event = ArcheryEvent::find($parameters->get("event_id"));
            if (!$event) {
                throw new BLoCException("event tidak ada");
            }

            $early_bird = 0;
            $end_date_early_bird = null;
            if (($data['early_bird'] > 0) && ($data['end_date_early_bird'] == null)) {
                throw new BLoCException("harap atur tanggal early bird");
            }

            if (($data['end_date_early_bird'] != null) && ($data['early_bird'] == 0)) {
                throw new BLoCException("harap inputkan harga early bird");
            }

            if (($data['early_bird'] > 0) && ($data['end_date_early_bird'] != null)) {
                $carbon_early_bird_datetime = Carbon::parse($data['end_date_early_bird']);
                $carbon_registration_start_datetime = Carbon::parse($event->registration_start_datetime);
                $carbon_registration_end_datetime = Carbon::parse($event->registration_end_datetime);

                $carbon_registration_start_date = Carbon::create($carbon_registration_start_datetime->year, $carbon_registration_start_datetime->month, $carbon_registration_start_datetime->day, 0, 0, 0);
                $carbon_registration_end_date = Carbon::create($carbon_registration_end_datetime->year, $carbon_registration_end_datetime->month, $carbon_registration_end_datetime->day, 0, 0, 0);


                $check = Carbon::create($carbon_early_bird_datetime->year, $carbon_early_bird_datetime->month, $carbon_early_bird_datetime->day, 0, 0, 0)
                    ->between($carbon_registration_start_date, $carbon_registration_end_date);

                if (!$check) {
                    throw new BLoCException("tanggal early bird harus berada pada rentang tanggal pendaftaran");
                }

                $early_bird = $data['early_bird'];
                $end_date_early_bird = $data['end_date_early_bird'];
            }

            $archery_event_category_detail_fee = ArcheryEventCategoryDetail::where('event_id', $parameters->get('event_id'))
                ->where('team_category_id', $data['team_category_id']);
            if (!empty($archery_event_category_detail_fee)) {
                $values = ArcheryEventCategoryDetail::where('event_id', $parameters->get('event_id'))
                    ->where('team_category_id', $data['team_category_id'])
                    ->update([
                        'fee' => $data['fee'],
                        'early_bird' => $early_bird,
                        'end_date_early_bird' => $end_date_early_bird
                    ]);
            } else {
                throw new BLoCException("team_category_id not found");
            }
        }
    }
}
