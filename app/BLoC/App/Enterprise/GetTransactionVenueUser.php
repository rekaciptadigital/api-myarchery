<?php

namespace App\BLoC\App\Enterprise;


use DAI\Utils\Abstracts\Retrieval;
use Illuminate\Support\Facades\DB;
use App\Models\VenuePlaceProduct;
use App\Models\VenuePlaceProductOrder;
use App\Models\VenuePlaceScheduleOperational;
use App\Models\VenuePlaceScheduleOperationalSession;
use Illuminate\Support\Facades\Auth;
use DAI\Utils\Exceptions\BLoCException;

class GetTransactionVenueUser extends Retrieval
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = Auth::guard('app-api')->user();
        $status = $parameters->get('status');

        $transactions = VenuePlaceProductOrder::select("venue_place_product_orders.*", "venue_places.name as venue_name", "venue_place_products.product_name as product_name", "venue_place_products.total_each_rent_per_day as total_each_rent_per_day", "venue_place_products.base_product as base_product")
                            ->join("venue_place_products", "venue_place_products.id", "=", "venue_place_product_orders.product_id")
                            ->join("venue_places", "venue_places.id", "=", "venue_place_products.place_id")
                            ->join("transaction_logs", "transaction_logs.id", "=", "venue_place_product_orders.transaction_log_id")
                            ->where(function ($query) use ($status) {
                                $query->where('transaction_logs.status', $status);
                            })
                            ->where('user_id', $user->id)
                            ->get();

        return $transactions;
    }

    protected function validation($parameters)
    {
        return [];
    }
}
