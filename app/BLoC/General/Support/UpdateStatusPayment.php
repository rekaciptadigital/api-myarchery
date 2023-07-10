<?php

namespace App\BLoC\General\Support;

use App\Libraries\PaymentGateWay;
use App\Models\TransactionLog;
use DAI\Utils\Abstracts\Transactional;

class UpdateStatusPayment extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $trans_log = TransactionLog::where("gateway", "OY")->where("status", 4)->get();
        foreach ($trans_log as $key => $value) {
            try {
                echo "order id : " . $value->order_id . "\n";
                $checkout = PaymentGateWay::notificationCallbackPaymnetOy($value->order_id);
                print_r($checkout);
                echo "\n\n";
            } catch (\Throwable $th) {
                $th->getMessage();
                continue;
            }
        }
    }

    protected function validation($parameters)
    {
        return [];
    }
}
