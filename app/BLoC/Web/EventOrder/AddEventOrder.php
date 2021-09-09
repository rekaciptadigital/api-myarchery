<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEvent;
use DAI\Utils\Abstracts\Transactional;
use App\Libraries\PaymentGateWay;

class AddEventOrder extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $payment = PaymentGateWay::setTransactionDetail(80000)
                                    ->enabledPayments(["bca_va", "bni_va", "bri_va", "other_va", "gopay"])
                                    ->setCustomerDetails("bahdrul","suryadarmasaqti19@gmail.com","082284559567")
                                    ->addItemDetail("1",50000,"barang 1")
                                    ->addItemDetail("2",30000,"barang 2")
                                    ->CreateSnap();
        return $payment;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
