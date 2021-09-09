<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Transactional;
use App\Libraries\PaymentGateWay;

class DetailEventOrder extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $payment = PaymentGateWay::setTransactionDetail(80000)
                                    ->setCustomerDetails("bahdrul","suryadarmasaqti19@gmail.com","082284559567")
                                    ->CreateSnap();
        return $payment;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
