<?php

namespace App\BLoC\Web\EventOrder;

use App\Libraries\PaymentGateWay;
use DAI\Utils\Abstracts\Retrieval;

class GetEventOrder extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $payment = PaymentGateWay::setTransactionDetail(80000)
            ->setCustomerDetails("bahdrul", "suryadarmasaqti19@gmail.com", "082284559567")
            ->createSnap();
        return $payment;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
