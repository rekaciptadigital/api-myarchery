<?php

namespace App\BLoC\Web\EventOrder;

use App\Libraries\PaymentGateWay;
use DAI\Utils\Abstracts\Retrieval;
use App\Models\ArcheryEvent;
use App\Models\TransactionLog;
use App\Models\ArcheryEventParticipant;
use App\Models\OrderEvent;
use Illuminate\Support\Facades\Auth;

class GetEventOrder extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $status = $parameters->get('status');
        $user = Auth::guard('app-api')->user();
        $output = array();

        // update orderEvent
        $participant_to_update = ArcheryEventParticipant::select("archery_event_category_details.fee", "archery_event_participants.*")
            ->join("archery_event_category_details", "archery_event_category_details.id", "=", "archery_event_participants.event_category_id")
            ->where("user_id", $user->id)
            ->where("order_event_id", 0)
            ->get();

        if (count($participant_to_update) > 0) {
            foreach ($participant_to_update as $key => $p) {
                $order_event = OrderEvent::saveOrderEvent($user->id, $p->status, $p->fee, $p->transaction_log_id, 0, $p->event_id);
                $p->order_event_id = $order_event->id;
                $p->save();
            }
        }

        $orders = OrderEvent::select("order_events.*")
            ->leftJoin("transaction_logs", "transaction_logs.id", "=", "order_events.transaction_log_id")
            ->where("order_events.user_id", $user->id)
            ->where("order_events.status", "!=", 6);

        $orders->when($status, function ($query) use ($status) {
            if ($status == 'pending') {
                return $query->where("order_events.status", 4)
                    ->where('transaction_logs.status', 4)
                    ->where('transaction_logs.expired_time', '>', time());
            }

            if ($status == 'success') {
                return $query->where('order_events.status', 1);
            }

            if ($status == 'expired') {
                return $query->where(function ($q) {
                    return $q->where("order_events.status", 2)->orWhere(function ($qr) {
                        return $qr->where("order_events.status", 4)
                            ->where('transaction_logs.status', 4)
                            ->where('transaction_logs.expired_time', '<', time());
                    });
                });
            }
        });

        $data = $orders->orderBy('order_events.id', 'desc')->get();

        foreach ($data as $key => $d) {
            $archery_event = ArcheryEvent::find($d->event_id);
            $transaction_info = PaymentGateWay::transactionLogPaymentInfo($d->transaction_log_id);
            $status_label = TransactionLog::getStatus($d->status);
            $output[] = [
                "archery_event" => $archery_event,
                "order_id" => $d->id,
                "transaction_info" => $transaction_info,
                "status_label" => $status_label,
                "order_date" => $transaction_info != false ? $transaction_info->order_date->format("Y-m-d H:i:s") : $d->created_at->format("Y-m-d H:i:s"),
                "status_id" => $d->status
            ];
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
