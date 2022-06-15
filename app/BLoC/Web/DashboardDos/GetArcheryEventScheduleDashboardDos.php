<?php

namespace App\BLoC\Web\DashboardDos;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventQualificationTime;
use DAI\Utils\Abstracts\Transactional;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Carbon;
use Carbon\CarbonPeriod;

class GetArcheryEventScheduleDashboardDos extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $event_id = $parameters->get('event_id');
        $archery_event = ArcheryEvent::where('id', $event_id)->first();
        $archery_category_detail = ArcheryEventQualificationTime::getQualificationById($event_id, null, null);

        $event_start_datetime = date(Carbon::parse($archery_event->event_start_datetime)->format('Y-m-d'));
        $event_end_datetime = date(Carbon::parse($archery_event->event_end_datetime)->format('Y-m-d'));
        $period = CarbonPeriod::create($event_start_datetime, $event_end_datetime);

        $output = [];
        foreach ($period as $date) {
            $schedule = [];
            foreach ($archery_category_detail as $key => $value) {
                $category_detail = ArcheryEventCategoryDetail::find($value["category_detail_id"]);
                $date_start = date(Carbon::parse($value['event_start_datetime'])->format('Y-m-d'));
                if ($date_start == $date->format('Y-m-d') ) {
                    $schedule[] = [
                        'event_id' => $value['event_id'],
                        'event_start_datetime' => $value['event_start_datetime'],
                        'event_end_datetime' => $value['event_end_datetime'],
                        'category_detail_label' => $category_detail->label_category
                    ];
                }
            }

            $output[] = [
                'date' => $date->format('Y-m-d'),
                'date_formatted' => dateFormatTranslate($date->format('l-d-F-Y')),
                'status' => $this->getStatus($date->format('Y-m-d')),
                'schedule' => $schedule
            ];
        }

        return $output;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required",
        ];
    }

    private function getStatus($date)
    {
        $interval_day = Carbon::parse($date)->diffInDays(Carbon::today());

        if ($date < Carbon::today()) {
            return 'Selesai';
        } elseif ($date == Carbon::today()) {
            return 'Sedang Berlangsung';
        } else {
            if ($interval_day == 1) {
                return 'Besok';
            } else {
                return $interval_day . ' hari lagi';
            }
        }
    }
}
