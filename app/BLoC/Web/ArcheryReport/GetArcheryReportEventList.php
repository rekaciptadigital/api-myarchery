<?php

namespace App\BLoC\Web\ArcheryReport;

use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\App;
use Response;
use Illuminate\Support\Facades\Redis;



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
        $response = [];
        foreach($type as $key => $value) {
            if (array_key_exists($value, $report_generate_dates)) {
                $date = $report_generate_dates[$value];
            }

            $response[] = [
                'report_type' => $value,
                'date_generate' => $date
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
