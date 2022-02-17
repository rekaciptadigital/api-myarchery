<?php

namespace App\BLoC\App\ArcheryEventOfficial;

use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventOfficial;
use App\Models\ArcheryEventOfficialDetail;
use App\Models\TransactionLog;
use App\Models\User;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;

class GetOrderEventOfficial extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user_login = Auth::guard('app-api')->user();
        $status = $parameters->get('status');
        $output = [];

        $archery_event_official = ArcheryEventOfficial::where('user_id', $user_login->id)->orderBy('id', 'DESC');
        $archery_event_official->when($status, function ($query) use ($status) {
            if ($status == 'pending') {
                return $query->select('archery_event_official.*')->join('transaction_logs', 'transaction_logs.id', '=', 'archery_event_official.transaction_log_id')
                    ->where('transaction_logs.status', 4)->where('transaction_logs.expired_time', '>', time());
            }
            if ($status == 'success') {
                return $query->where('archery_event_official.status', 1);
            }

            if ($status == 'expired') {
                return $query->select('archery_event_official.*')->join('transaction_logs', 'transaction_logs.id', '=', 'archery_event_official.transaction_log_id')
                    ->where('transaction_logs.status', 4)->where('transaction_logs.expired_time', '<', time());
            }
        });

        $data = [];
        $col_archery_event_official = $archery_event_official->get();
        if ($col_archery_event_official->count() > 0) {
            foreach ($col_archery_event_official as $aeo) {
                $data['detail_archery_event_official'] = ArcheryEventOfficial::getDetailEventOfficialById($aeo->id);
                $data['archery_event_official_detail'] = ArcheryEventOfficialDetail::getEventOfficialDetailById($aeo->event_official_detail_id);
                $data['club'] = ArcheryClub::find($aeo->club_id);
                $data['transaction_log_info'] = TransactionLog::getTransactionInfoByid($aeo->transaction_log_id);
                array_push($output, $data);
            }
        }

        return $output;
    }

    protected function validation($parameters)
    {
        return [
            'status' => 'in:success,pending,expired'
        ];
    }
}
