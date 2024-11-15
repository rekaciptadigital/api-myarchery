<?php

namespace App\BLoC\App\Webhook;

use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Abstracts\Transactional;
use DAI\Utils\Exceptions\BLoCException;

class OyCreatePaymentLink extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $data = [
            'partner_tx_id' => $parameters->get('partner_tx_id'),
            'description' => $parameters->get('description'),
            'list_disabled_payment_methods' => $parameters->get('list_disabled_payment_methods') . ',OFFLINE_CASH_IN,CREDIT_CARD',
            'sender_name' => $parameters->get('sender_name'),
            'email' => $parameters->get('email'),
            'phone_number' => $parameters->get('phone_number'),
            'sender_name' => $parameters->get('sender_name'),
            'list_enabled_banks' => '002,008,009,013,022,213,014,QRIS',
            'list_enabled_ewallet' => 'shopeepay_ewallet,dana_ewallet,linkaja_ewallet,ovo_ewallet',
            'amount' => $parameters->get('amount'),
            "include_admin_fee" => false,
            "is_open" => false,
            'expiration' => $parameters->get('expiration'),
        ];

        $http_client = new \GuzzleHttp\Client();

        $url = env('OY_BASEURL', "https://api-stg.oyindonesia.com") . '/api/payment-checkout/create-v2';

        $response = $http_client->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-oy-username' => env('OYID_USERNAME', "myarchery"),
                'x-api-key' => env('OYID_APIKEY', "4044e330-90e2-4a01-8afa-1c432a8c140e"),
            ],
            'json' => $data,
            'timeout' => 50,
        ]);

        $response_body = (string) $response->getBody();

        $result = json_decode($response_body);

        return [
            'result' => $result,
        ];
    }

    protected function validation($parameters)
    {
        return [
            "partner_tx_id" => "required|string",
            "description" => "required|string",
            "list_disabled_payment_methods" => "string",
            "sender_name" => "required|string",
            "email" => "required|string|email",
            "phone_number" => "required|string",
            "amount" => "required|integer",
            "expiration" => "required|string",
        ];
    }
}
