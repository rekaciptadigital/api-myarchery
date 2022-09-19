<?php

namespace App\BLoC\App\EventOrder;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventOfficial;
use App\Models\TransactionLog;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;
use App\Models\ArcheryEventParticipant;
use DAI\Utils\Exceptions\BLoCException;

class GetEventOrderV2 extends Retrieval
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

        // official
        $archery_event_official = ArcheryEventOfficial::where('user_id', $user_login->id)->where("archery_event_official.status", "!=", 6)->orderBy('id', 'DESC');
        $archery_event_official->when($status, function ($query) use ($status) {
            if ($status == 'pending') {
                return $query->select('archery_event_official.*')
                    ->join('transaction_logs', 'transaction_logs.id', '=', 'archery_event_official.transaction_log_id')
                    ->where("archery_event_official.status", 4)
                    ->where('transaction_logs.status', 4)
                    ->where('transaction_logs.expired_time', '>', time());
            }
            if ($status == 'success') {
                return $query->where('archery_event_official.status', 1);
            }

            if ($status == 'expired') {
                return $query->select('archery_event_official.*')->join('transaction_logs', 'transaction_logs.id', '=', 'archery_event_official.transaction_log_id')
                    ->where(function ($q) {
                        return $q->where("archery_event_official.status", 2)
                            ->orWhere(function ($qr) {
                                return $qr->where('transaction_logs.status', 4)
                                    ->where('transaction_logs.expired_time', '<', time());
                            });
                    });
            }
        });

        $data = [];
        $col_archery_event_official = $archery_event_official->get();
        if ($col_archery_event_official->count() > 0) {
            foreach ($col_archery_event_official as $aeo) {
                $data['detail_order'] = [
                    "id" => $aeo->id,
                    "type" => "official"
                ];
                $data['transaction_log_info'] = TransactionLog::getTransactionInfoByid($aeo->transaction_log_id);
                $data['detail_event'] = ArcheryEvent::select('archery_events.*')->leftJoin('archery_event_official_detail', 'archery_events.id', '=', 'archery_event_official_detail.event_id')
                    ->where('archery_event_official_detail.id', $aeo->event_official_detail_id)
                    ->first();
                $data['participant'] = [];

                array_push($output, $data);
            }
        }

        // peserta
        $participants = ArcheryEventParticipant::where("user_id", $user_login->id)->where("archery_event_participants.status", "!=", 6);
        $participants->when($status, function ($query) use ($status) {
            if ($status == 'pending') {
                return $query->select('archery_event_participants.*')->join('transaction_logs', 'transaction_logs.id', '=', 'archery_event_participants.transaction_log_id')
                    ->where("archery_event_participants.status", 4)
                    ->where('transaction_logs.status', 4)
                    ->where('transaction_logs.expired_time', '>', time());
            }
            if ($status == 'success') {
                return $query->where('archery_event_participants.status', 1);
            }

            if ($status == 'expired') {
                return $query->select('archery_event_participants.*')->join('transaction_logs', 'transaction_logs.id', '=', 'archery_event_participants.transaction_log_id')
                    ->where(function ($q) {
                        return $q->where("archery_event_participants.status", 2)
                            ->orWhere(function ($qr) {
                                return $qr->where("archery_event_participants.status", 4)
                                    ->where("transaction_logs.status", 4)
                                    ->where("transaction_logs.expired_time", "<", time());
                            });
                    });
            }
        });

        $data_event = $participants->orderBy('archery_event_participants.id', 'desc')->get();

        if ($data_event->count() > 0) {
            foreach ($data_event as $de) {
                $data['detail_order'] = [
                    "id" => $de->id,
                    "type" => "event"
                ];
                $data['transaction_log_info'] = TransactionLog::getTransactionInfoByid($de->transaction_log_id);
                $detail_event = ArcheryEvent::find($de->event_id);
                if (!$detail_event) {
                    throw new BLoCException("event not found");
                }
                $data['participant'] = $de;
                $data["detail_event"] = $detail_event;
                $category_label = $de->team_category_id . "-" . $de->age_category_id . "-" . $de->competition_category_id . "-" . $de->distance_id . "m";

                $data['category'] = [
                    "team_category_id" => $de->team_category_id,
                    "age_category_id" => $de->age_category_id,
                    "competition_category_id" => $de->competition_category_id,
                    "distance_id" => $de->distance_id,
                    "label" => $category_label
                ];
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
