<?php

namespace App\BLoC\App\EventOrder;

use App\Models\TransactionLog;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;
use App\Models\ArcheryEventParticipant;
use App\Models\OrderEvent;
use DAI\Utils\Exceptions\BLoCException;

class CancelBuyEvent extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user_login = Auth::guard('app-api')->user();
        $order_event_id = $parameters->get("order_event_id");

        $order_event = OrderEvent::select(
            "order_events.*",
            "transaction_logs.id as transaction_id",
            "transaction_logs.status as transaction_status",
            "transaction_logs.expired_time as transaction_expired_time"
        )->leftJoin(
            "transaction_logs",
            "transaction_logs.id",
            "=",
            "order_events.transaction_log_id"
        )
            ->where("order_events.id", $order_event_id)
            ->first();

        if (
            $order_event->status == 1 ||
            $order_event->status == 4 &&
            $order_event->transaction_status == 4 &&
            $order_event->transaction_expired_time > time()
        ) {
            $order_event->status = 3;
            $order_event->save();

            $participant = ArcheryEventParticipant::where("order_event_id", $order_event_id)
                ->get();

            if (count($participant) > 0) {
                foreach ($participant as $key => $p) {
                    $p->status = 3;
                    $p->save();
                }
            }

            $transaction_log = TransactionLog::find($order_event->transaction_id);
            if ($transaction_log) {
                $transaction_log->status = 3;
                $transaction_log->save();
            }
        } else {
            throw new BLoCException("invalid status to cancel");
        }

        return "success cancel";
    }

    protected function validation($parameters)
    {
        return [
            "order_event_id" => "required|exists:order_events,id"
        ];
    }
}
