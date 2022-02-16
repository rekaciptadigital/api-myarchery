<?php

namespace App\BLoC\App\ArcheryEventOfficial;

use App\Libraries\PaymentGateWay;
use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventOfficial;
use App\Models\ArcheryEventOfficialDetail;
use App\Models\TransactionLog;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Exceptions\BLoCException;


class GetEventOfficialDetail extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $event_id = $parameters->get('event_id');
        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("Event tidak ditemukan");
        }

        $event_official_detail = ArcheryEventOfficialDetail::where('event_id', $event->id)->first();
        if (!$event_official_detail) {
            throw new BLoCException("official detail belum di atur pada event ini");
        }

        $output = [
            'event_official_detail' => ArcheryEventOfficialDetail::getEventOfficialDetailById($event_official_detail->id)
        ];

        return $output;
    }

    protected function validation($parameters)
    {
        return [
            'event_id' => 'required|integer',
        ];
    }
}
