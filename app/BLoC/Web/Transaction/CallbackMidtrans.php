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
        /* 
            db insert :
                        - archery_event_participant_member_numbers
                        - archery_event_sualification_schedule_full_days
                        - participant_member_teams
        */    
    }

    protected function process($parameters)
    {
        $gateway = $parameters->get("gateway");
        if($gateway == "OY"){
            return PaymentGateWay::notificationCallbackPaymnetOy($parameters);
        }
        return PaymentGateWay::notificationCallbackPaymnet();
    }

    protected function validation($parameters)
    {
        return [];
    }
}
