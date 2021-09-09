<?php

namespace App\BLoC\Web\Transaction;

use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Transactional;
use App\Libraries\PaymentGateWay;

class CallbackMidtrans extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $payment = PaymentGateWay::NotificationCallbackPaymnet();
        return $payment;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
