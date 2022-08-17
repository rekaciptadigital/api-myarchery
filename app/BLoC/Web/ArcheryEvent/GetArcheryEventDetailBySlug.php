<?php

namespace App\BLoC\Web\ArcheryEvent;

use App\Models\ArcheryEvent;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\Auth;

class GetArcheryEventDetailBySlug extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $admin = Auth::user();
        $archery_event = ArcheryEvent::where('event_slug', $parameters->get('slug'))->first();
        if (!$archery_event) {
            throw new BLoCException("Data not found");
        }

        $archery_event_detail = ArcheryEvent::detailEventById($archery_event->id, 1);

        $have_paymentgateway_fee =false;
        if($archery_event->include_payment_gateway_fee_to_user == 1)
            $have_paymentgateway_fee = true;
        $archery_event_detail["payment_methode"] = PaymentGateWay::getPaymentMethode($have_paymentgateway_fee);
        
        return $archery_event_detail;
    }

    protected function validation($parameters)
    {
        return [
            'slug' => 'required',
        ];
    }
}