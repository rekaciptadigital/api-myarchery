<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Models\TransactionLog;
use App\Libraries\PaymentGateWay;

class CheckStatusPaymentOy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:StatusOy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'set status OY';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $trans_log = TransactionLog::where("gateway","OY")->where("status", 4)->get();
        foreach ($trans_log as $key => $value) {
            echo "order id : ".$value."\n";
            $checkout = PaymentGateWay::notificationCallbackPaymnetOy($order_id);
            print_r($checkout);
            echo "\n\n";
        }
    }
}
