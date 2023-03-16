<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderEvent extends Model
{
    protected $table = 'order_events';
    protected $guarded = ["id"];

    public static function saveOrderEvent(int $user_id, int $status, int $total_price, int $transaction_log_id = 0, int $is_early_bird_payment = 0, int $event_id = 0)
    {
        $order_event = new OrderEvent();
        $order_event->user_id = $user_id;
        $order_event->status = $status;
        $order_event->transaction_log_id = $transaction_log_id;
        $order_event->total_price = $total_price;
        $order_event->is_early_bird_payment = $is_early_bird_payment;
        $order_event->event_id = $event_id;
        $order_event->save();

        return $order_event;
    }
}
