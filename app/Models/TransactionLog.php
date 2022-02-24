<?php

namespace App\Models;

use App\Libraries\PaymentGateWay;
use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{
    protected $guarded = ["id"];

    protected $status = [
        4 => "Menunggu Pembayaran",
        1 => "Di Ikuti",
        2 => "Kadarluarsa",
        3 => "Gagal",
        5 => "Refund"
    ];

    protected function getStatus($status_id)
    {
        return isset($this->status[$status_id]) ? $this->status[$status_id] : "none";
    }

    public static function getTransactionInfoByid($transaction_log_id)
    {
        $data = [];
        $transaction_log = TransactionLog::find($transaction_log_id);
        if ($transaction_log) {
            $data = PaymentGateWay::transactionLogPaymentInfo($transaction_log_id);
        }

        return $data;
    }
}
