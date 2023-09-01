<?php

namespace App\BLoC\App\Webhook;

use DAI\Utils\Abstracts\Retrieval;

class OyCreatePaymentLink extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        return "";
    }

    protected function validation($parameters)
    {
        return [
            'name' => 'required|string',
            'email' => 'required|string|email',
            'phone_number' => 'required|string',
            'amount' => 'required|integer',
        ];
    }
}
