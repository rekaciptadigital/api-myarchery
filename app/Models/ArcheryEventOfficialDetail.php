<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryEventOfficialDetail extends Model
{
    protected $table = 'archery_event_official_detail';
    protected $guarded = 'id';

    public static function getEventOfficialDetailById($official_detail_id)
    {
        $official_detail = ArcheryEventOfficialDetail::find($official_detail_id);
        $data = [];
        if ($official_detail) {
            $event_detail = [];
            $event = ArcheryEvent::find($official_detail->event_id);
            if ($event) {
                $event_detail = $event->getDetailEventById($event->id);
            }
            $data = [
                'event_official_detail_id' => $official_detail->id,
                'quota' => $official_detail->quota,
                'fee' => $official_detail->fee,
                'detail_event' => $event_detail
            ];
        }
        return $data;
    }
}
