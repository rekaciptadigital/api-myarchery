<?php

namespace App\BLoC\Web\ArcheryReport;

use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\App;
use Response;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Carbon;
use App\Models\ArcheryEvent;


class GetArcheryReportEventList extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $event_id = $parameters->get('event_id');
        if (!$event_id) {
            throw new BLoCException("Event tidak ditemukan");
        }

        $type = ['participant', 'finance', 'competition'];
        $key = env("REDIS_KEY_PREFIX") . ":report:date-generate:event-" . $event_id . ":updated";
        $report_generate_dates = Redis::hgetall($key);

        $date = null;
        $is_available = true;
        $response = [];
        foreach($type as $key => $value) {
            if (array_key_exists($value, $report_generate_dates)) {
                $date = $report_generate_dates[$value];
            }

            if ($value == 'finance') {
                $is_available = false;
            }

            if ($value == 'competition') {
                $event = ArcheryEvent::find($event_id);
                $today = (Carbon::now())->toDateTimeString();
                $carbon_end_date = Carbon::parse($event->event_end_datetime);

                // if($today < $carbon_end_date) {
                //     $is_available = false;
                // } else {
                //     $is_available = true;
                // }

                // unlock case download hanya bisa setelah event berakhir
                $is_available = true;
            }
           
            $response[] = [
                'report_type' => $value,
                'date_generate' => $date,
                'is_available' => $is_available
            ];
        }

        return $response;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => 'required|integer'
        ];
    }


}
