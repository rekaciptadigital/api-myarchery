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
use Illuminate\Support\Facades\Auth;

class GetDetailOrderOfficial extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = Auth::guard('app-api')->user();
        $event_official_id = $parameters->get('event_official_id');
        $event_official = ArcheryEventOfficial::find($event_official_id);
        if (!$event_official) {
            throw new BLoCException("data pesanan tidak ditemukan");
        }

        if ($event_official->user_id != $user->id) {
            throw new BLoCException("forbiden");
        }

        $club = ArcheryClub::find($event_official->club_id);


        $output = [
            'detail_event_official' => ArcheryEventOfficial::getDetailEventOfficialById($event_official->id),
            'transaction_info' => TransactionLog::getTransactionInfoByid($event_official->transaction_log_id),
            'event_official_detail' => ArcheryEventOfficialDetail::getEventOfficialDetailById($event_official->event_official_detail_id),
            'detail_user' => User::getDetailUser($event_official->user_id),
            'club_detail' => $club
        ];

        return $output;
    }

    protected function validation($parameters)
    {
        return [
            'event_official_id' => 'required|integer',
        ];
    }
}
