<?php

namespace App\BLoC\App\Enterprise;


use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use App\Models\VenuePlaceProduct;
use App\Models\VenuePlaceProductOrder;
use App\Libraries\PaymentGateWay;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;

class AddOrderVenueProduct extends Retrieval
{
    var $gateway = "";
    var $have_fee_payment_gateway = false;
    var $payment_methode = "";

    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = Auth::guard('app-api')->user();

        $venue_place_product = VenuePlaceProduct::find($parameters->get('product_id'));
        if (!$venue_place_product) throw new BLoCException("Data product not found");

        \DB::beginTransaction();
        try {
            // insert venue product
            $product_order = VenuePlaceProductOrder::create([
                "product_id" => $parameters->get('product_id'),
                "operational_session_id" => $parameters->get('operational_session_id'),
                "user_id" => $user->id,
                "booking_date" => $parameters->get('booking_date')
            ]);

            $price = $parameters->get('price');
            $order_id = env("ORDER_VENUE_ID_PREFIX", "OV-MA") . $product_order->id;

            $payment = PaymentGateWay::setTransactionDetail((int)$price, $order_id)
                        ->setGateway($this->gateway)
                        ->setCustomerDetails($user->name, $user->email, $user->phone_number)
                        ->addItemDetail($venue_place_product->id, (int)$price, $venue_place_product->product_name)
                        ->enabledPaymentWithFee($this->payment_methode, $this->have_fee_payment_gateway)
                        ->createSnap();
            if(!$payment->status)
                throw new BLoCException($payment->message);

            $product_order->update([
                'transaction_log_id' => $payment->transaction_log_id
            ]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw new BLoCException($th->getMessage());
        }

        $response = [
            "product_order" => $product_order->id,
            "payment_info" => $payment
        ];
        return $this->composeResponse($response);
    }

    protected function validation($parameters)
    {
        return [
            'product_id' => 'required|integer',
            'operational_session_id' => 'required|integer',
            'booking_date' => 'required',
            'price' => 'required',
        ];
    }

    private function composeResponse(array $res)
    {
        return [
            "product_order_id" => $res["product_order"],
            "payment_info" => $res["payment_info"]
        ];
    }
}
