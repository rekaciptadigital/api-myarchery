<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UrlReport extends Model
{
    protected $table = "url_reports";

    protected $fillable = ["type", "url", "event_id"];

    public static function removeAllUrlReport($event_id)
    {
        $report_event = UrlReport::where("event_id", $event_id)->where("type", "report_event")->first();
        if ($report_event) {
            $report_event->delete();
        }

        $upp = UrlReport::where("event_id", $event_id)->where("type", "upp")->first();
        if ($upp) {
            $upp->delete();
        }

        $medal_recapitulation = UrlReport::where("event_id", $event_id)->where("type", "medal_recapitulation")->first();
        if ($medal_recapitulation) {
            $medal_recapitulation->delete();
        }
    }
}
