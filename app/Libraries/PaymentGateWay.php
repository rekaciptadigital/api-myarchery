<?php

namespace App\Libraries;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventOfficial;
use App\Models\TransactionLog;
use App\Models\EoCashFlow;

use Illuminate\Support\Facades\Storage;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipantMemberNumber;
use App\Models\ArcheryEventParticipantNumber;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\ArcheryEventQualificationTime;
use App\Models\ClubMember;
use App\Models\ArcherySeriesUserPoint;
use App\Models\ParticipantMemberTeam;
use App\Models\TemporaryParticipantMember;
use App\Models\User;
use App\Models\Admin;
use App\Models\ArcheryEventOfficialDetail;
use App\Models\ArcheryEvent;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Redis;

class PaymentGateWay
{

    static $transaction_details = array();
    static $customer_details = array();
    static $payment_gateway_fee = 0;
    static $fee_payment_gateway_to_user = false;
    static $fee_myarchery = 0;
    static $total_amount = 0;
    static $have_fee_myarchery = false;
    static $gateway = "";
    static $token = "";
    static $oy_callback = array();
    static $payment_methode = "";
    static $bank_sender = "";
    static $sender_bank = "";
    static $expired_time = "";
    static $payment_methode_detail = [];
    static $enabled_payments = [
        "bca_klikbca", "bca_klikpay", "bri_epay", "echannel", "permata_va",
        "bca_va", "bni_va", "bri_va", "other_va", "indomaret"
    ];
    static $item_details = array();

    public function __construct()
    {
        // Set your Merchant Server Key
        \Midtrans\Config::$serverKey = env("MIDTRANS_SERVER_KEY");
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        \Midtrans\Config::$isProduction = env("MIDTRANS_IS_PROD");
        // Set sanitization on (default)
        \Midtrans\Config::$isSanitized = true;
        // Set 3DS transaction for credit card to true
        \Midtrans\Config::$is3ds = true;
    }

    public static function setTransactionDetail($gross_amount, $order_id = "")
    {
        $This = (new self);
        if (empty($order_id)) {
            $order_id = rand();
        };

        self::$transaction_details = array(
            'order_id' => $order_id,
            'gross_amount' => $gross_amount,
        );
        return (new self);
    }

    public static function setMyarcheryFee($fee)
    {
        if($fee > 0){
            self::$fee_myarchery = $fee;
            self::$have_fee_myarchery = true;
            self::addItemDetail("MA-fee", $fee, "myArchey fee", "", 1, "", "");
        }
        return (new self);
    }

    public static function setGateway(string $gateway)
    {
        if(!empty($gateway))
        self::$gateway = $gateway;
        else
            self::$gateway = env("PAYMENT_GATEWAY","midtrans");
        return (new self);
    }

    public static function enabledPayments(array $payments)
    {
        self::$enabled_payments = $payments;
        return (new self);
    }

    public static function feePaymentsToUser(bool $is_fee)
    {
        self::$fee_payment_gateway_to_user = $is_fee;
        return (new self);
    }

    // sampel payments
    // ["credit_card", "cimb_clicks",
    // "bca_klikbca", "bca_klikpay", "bri_epay", "echannel", "permata_va",
    // "bca_va", "bni_va", "bri_va", "other_va", "gopay", "indomaret",
    // "danamon_online", "akulaku", "shopeepay"]
    public static function getPaymentFee(string $payment_methode,$sender_bank,$amount,bool $have_fee = false)
    {
        if(empty(self::$gateway))
            self::$gateway = env("PAYMENT_GATEWAY","midtrans");
        self::$payment_methode = $payment_methode;
        self::$sender_bank = $sender_bank;
        return self::_feePaymentMethode($have_fee,$amount);
    }

    public static function getPaymentMethode(bool $have_fee = false)
    {
        if(empty(self::$gateway))
            self::$gateway = env("PAYMENT_GATEWAY","midtrans");
        
        $list = self::_listPaymentMethode($have_fee);
    
        return $list[self::$gateway];
    }

    private static function _listPaymentMethode(bool $have_fee = false){
        return [
            "midtrans" => [
                "have_fee" => $have_fee,
                "default" => [
                    "id" => "default",
                    "label" => "Default",
                    "list" => ["bca_va", "bni_va", "bri_va", "gopay", "other_va"],
                    "fee_type" => "nominal",
                    "active" => $have_fee,
                    "fee" => 0,
                ],
                "bankTransfer" => [
                    "id" => "bank_transfer",
                    "label" => "Transfer Bank",
                    "list" => [""],
                    "fee_type" => "nominal",
                    "active" => $have_fee,
                    "fee" => 4000,
                ],
                "gopay" => [
                    "id" => "gopay",
                    "label" => "Gopay",
                    "list" => ["gopay"],
                    "fee_type" => "percentage",
                    "active" => $have_fee,
                    "fee" => 2,
                ],
                "qris" => [
                    "id" => "qris",
                    "label" => "Qris",
                    "list" => ["qris"],
                    "fee_type" => "percentage",
                    "active" => $have_fee,
                    "fee" => 0.7
                ],
            ],
            "OY" => [
                "have_fee" => $have_fee,
                "default" => [
                    "id" => "default",
                    "label" => "Default",
                    "list" => ["bca_va", "bni_va", "bri_va", "gopay", "other_va"],
                    "fee_type" => "nominal",
                    "active" => $have_fee,
                    "fee" => 0,
                ],
                "bankTransfer" => [
                    "id" => "VA",
                    "label" => "Transfer Bank",
                    "list" => ["002","008","009","013","022"],
                    "fee_type" => "nominal",
                    "active" => $have_fee,
                    "fee" => 4440,
                ],
                "dana" => [
                    "id" => "EWALLET",
                    "label" => "Dana",
                    "list" => ["dana"],
                    "fee_type" => "percentage",
                    "active" => $have_fee,
                    "fee" => 2,
                ],
                "qris" => [
                    "id" => "QRIS",
                    "label" => "Qris",
                    "list" => ["qris"],
                    "fee_type" => "percentage",
                    "active" => $have_fee,
                    "fee" => 0.7
                ],
                ]
            ];
    }


    private static function _feePaymentMethode($have_fee, $amount){
        if(!$have_fee){
            return 0;
        }
        $fee = 0;
        $list = ["OY" => [
                    "VA" => [
                        "002" => 3500,
                        "013" => 3500,
                        "008" => 3500,
                        "022" => 3500,
                        "009" => 3500,
                        "014" => 4500,
                        "type" => "nominal"
                    ],
                    "QRIS" => [
                        "all" => 0.7,
                        "type" => "percentage"
                    ],
                    "EWALLET" => [
                        "dana" => 1.5,
                        "linkaja" => 1.5,
                        "shopeepay" => 2,
                        "ovo" => 1.5,
                        "type" => "percentage"
                    ]
                ]
            ];
        if(!isset($list[self::$gateway])){
            return 0;
        }
        $type = $list[self::$gateway][self::$payment_methode]["type"];
        $sender_bank = self::$payment_methode == "QRIS" ? "all" : self::$sender_bank;
        $n = $list[self::$gateway][self::$payment_methode][$sender_bank];
        if($type == "percentage"){
            $fee = round($amount * ($n/100));
        }
        if($type == "nominal"){
            $fee = $n;
        }

        return $fee;
    }

    public static function addItemDetail($id, $price, $name, $brand = "", $quantity = 1, $category = "", $merchant_name = "")
    {
        self::$item_details[] = array(
            "id" => $id,
            "price" => $price,
            "quantity" => $quantity,
            "name" => substr($name, 0, 50),
            "brand" => $brand,
            "category" => $category,
            "merchant_name" => $merchant_name
        );
        return (new self);
    }

    public static function setCustomerDetails($name, $email, $phone)
    {
        $name = self::splitName($name);
        self::$customer_details = array(
            'first_name' => $name[0],
            'last_name' => $name[1],
            'email' => $email,
            'phone' => $phone,
        );

        return (new self);
    }

    // uses regex that accepts any word character or hyphen in last name
    public static function splitName($name)
    {
        $name = trim($name);
        $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $first_name = trim(preg_replace('#' . preg_quote($last_name, '#') . '#', '', $name));
        return array($first_name, $last_name);
    }

    public static function createSnap()
    {
        if(empty(self::$gateway))
            self::$gateway = env("PAYMENT_GATEWAY","midtrans");

        $gateway = self::$gateway;
        switch ($gateway) {
            case 'midtrans':
                return self::createLinkPaymentMidtrans();
                break;
            
            default:
                return self::createLinkOY();
                break;
        }
    }

    public static function createLinkOY()
    {
        $customer_details = self::$customer_details;
        $expired_time = strtotime("+" . env("MIDTRANS_EXPIRE_DURATION_SNAP_TOKEN_ON_MINUTE", 90) . " minutes", time());
        self::$expired_time = $expired_time;
        $payment_methode_detail = self::$payment_methode_detail;
        $desc = "my archery product";
        $invoice_items = [];
        $amount = 0;
        foreach (self::$item_details as $key => $value) {
            if($value["id"] != "payment_fee"){
                $amount = $amount + $value["price"];
                $invoice_items[] = (object)[
                    "item"=>$value["name"], 
                    "description"=>$value["id"]." | ".$value["name"], 
                    "quantity"=> $value["quantity"], 
                    "date_of_purchase"=>date('Y-m-d H:i:s', time()), 
                    "price_per_item"=> $value["price"]  
                ];
            }
        }
        $total_amount = self::$transaction_details["gross_amount"];
        
        self::$total_amount = $total_amount;
        $body = [
            "description" => $desc,
            "partner_tx_id" => self::$transaction_details["order_id"],
            "notes" => "",
            "invoice_items" => $invoice_items,
            "sender_name" => $customer_details["first_name"]." ".$customer_details["last_name"],
            "amount" => $amount,
            'email' => $customer_details["email"],
            "phone_number" => $customer_details["phone"],
            "is_open" => false,
            "step" => "select-payment-method",
            "include_admin_fee" => self::$fee_payment_gateway_to_user ? true : false,
            "expiration" => date('Y-m-d H:i:s', $expired_time),
            "list_disabled_payment_methods" => implode(",",["CREDIT_CARD","DEBIT_CARD","OFFLINE_CASH_IN"])
        ];
        
        $client = new \GuzzleHttp\Client();
        $url = env('OY_BASEURL',"https://api-stg.oyindonesia.com") . '/api/payment-checkout/create-v2';
        $response = $client->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-oy-username' => env('OYID_USERNAME',"myarchery"),
                'x-api-key' => env('OYID_APIKEY',"4044e330-90e2-4a01-8afa-1c432a8c140e"),
            ],
            'json' => $body,
            'timeout' => 50,
        ]);

        $response_body = (string) $response->getBody();

        $result = json_decode($response_body);

        if ($result->status) {
            return self::saveLog($body,$result);
        }
        return (object)["status" => false, "message" => $result->message];
    }

    private static function saveLog($payment_gateway_params, $payment_gateway_response){
        if(empty(self::$gateway))
            self::$gateway = env("PAYMENT_GATEWAY","midtrans");
            
            $status = 4;
            $activity = array("request_snap_token" => array("params" => $payment_gateway_params, "response" => $payment_gateway_response));
            $transaction_log = new TransactionLog;
            $transaction_log->order_id = self::$transaction_details["order_id"];
            $transaction_log->transaction_log_activity = json_encode($activity);
            $transaction_log->opt = json_encode($payment_gateway_response);
            $transaction_log->amount = self::$transaction_details["gross_amount"];
            $transaction_log->status = $status;
            $transaction_log->expired_time = self::$expired_time;
            $transaction_log->gateway = self::$gateway;
            $transaction_log->include_my_archery_fee = self::$fee_myarchery;
            $transaction_log->token = self::$token;
            $transaction_log->total_amount =self::$total_amount;
            $transaction_log->save();

            return self::result($transaction_log->id, $status, $payment_gateway_response);
    }

    private static function result($transaction_log_id,$status, $opt = []){
        if(empty(self::$gateway))
            self::$gateway = env("PAYMENT_GATEWAY","midtrans");

        return (object)[
            "status" => true,
            "gateway" => self::$gateway,
            "order_id" => self::$transaction_details["order_id"],
            "total" => self::$total_amount,
            "status" => TransactionLog::getStatus($status),
            "transaction_log_id" => $transaction_log_id,
            "snap_token" => self::$token,
            "gateway" => self::$gateway,
            "optional" => $opt,
            "client_key" => env("MIDTRANS_CLIENT_KEY"),
            "client_lib_link" => env("MIDTRANS_CLIENT_LIB_LINK")
        ];
    }
    
    public static function createLinkPaymentMidtrans()
    {
        $transaction_details = self::$transaction_details;
        $customer_details = self::$customer_details;
        $expired_time = strtotime("+" . env("MIDTRANS_EXPIRE_DURATION_SNAP_TOKEN_ON_MINUTE", 30) . " minutes", time());
        $params = array(
            "expiry" => array(
                "unit" => "minutes",
                "duration" => env("MIDTRANS_EXPIRE_DURATION_SNAP_TOKEN_ON_MINUTE", 30)
            ),
            'transaction_details' => $transaction_details,
            'customer_details' => $customer_details,
            'item_details' => self::$item_details,
            'enabled_payments' => self::$enabled_payments
        );
        self::$expired_time = $expired_time;
        $snap_token = \Midtrans\Snap::getSnapToken($params);
        if ($snap_token) {
            self::$token = $snap_token;
            return self::saveLog($params,$snap_token);
        }
        return (object)["status" => false, "message" => $result->message];
    }

    public static function transactionLogPaymentInfo($transaction_log_id)
    {
        $transaction_log = TransactionLog::find($transaction_log_id);
        if (!$transaction_log) return false;

        $time_now = time();
        $status = $transaction_log->status == 4 && $transaction_log->expired_time <= $time_now ? 2 : $transaction_log->status;
        return (object)[
            "order_date" =>$transaction_log->created_at,
            "order_id" => $transaction_log->order_id,
            "total" => $transaction_log->total_amount == 0 ? $transaction_log->amount : $transaction_log->total_amount,
            "gateway" => $transaction_log->gateway,
            "opt" => json_decode($transaction_log->opt),
            "status_id" => $status,
            "status" => TransactionLog::getStatus($status),
            "transaction_log_id" => $transaction_log->id,
            "snap_token" => $transaction_log->token,
            "client_key" => env("MIDTRANS_CLIENT_KEY"),
            "client_lib_link" => env("MIDTRANS_CLIENT_LIB_LINK")
        ];
    }

    public static function notificationCallbackPaymnetOy($parameters){
        $order_id = $parameters->get('partner_tx_id');

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', env('OY_BASEURL',"https://api-stg.oyindonesia.com") . '/api/payment-checkout/status?send_callback=false&partner_tx_id=' . $order_id, [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-oy-username' => env('OYID_USERNAME',"myarchery"),
                'x-api-key' => env('OYID_APIKEY',"4044e330-90e2-4a01-8afa-1c432a8c140e"),
            ],
            'timeout' => 50,
        ]);

        $result = json_decode((string) $response->getBody());
        if (!$result->success) {
            return false;
        }
        
        $transaction_log = TransactionLog::where("order_id", $order_id)->first();
        if (!$transaction_log || $transaction_log->status == 1) {
            return false;
        }
        $status = 3;
        if ($result->data->status == 'complete') {
            $status = 1;
        } else if ($result->data->status == 'processing') {
            $status = 4;
            // $transaction_log->expired_time = strtotime("+" . env("MIDTRANS_EXPIRE_DURATION_SNAP_TOKEN_ON_MINUTE", 30) . " minutes", time());
        } else if ($result->data->status == 'expired') {
            $status = 2;
        }

        $transaction_log->status = $status;
        $activity = \json_decode($transaction_log->transaction_log_activity, true);
        $activity["notification_callback_" . $status] = \json_encode($result);
        $transaction_log->transaction_log_activity = \json_encode($activity);
        $transaction_log->save();

        if (substr($transaction_log->order_id, 0, strlen(env("ORDER_OFFICIAL_ID_PREFIX"))) == env("ORDER_OFFICIAL_ID_PREFIX")) {
            if ($status == 1) {
                return self::orderOfficial($transaction_log, $status);
            }
        } elseif (substr($transaction_log->order_id, 0, strlen(env("ORDER_ID_PREFIX"))) == env("ORDER_ID_PREFIX")) {
            ArcheryEventParticipant::where("transaction_log_id", $transaction_log->id)->update(["status" => $status]);
            if ($status == 1) {
                self::$oy_callback = $result->data;
                return self::orderEvent($transaction_log, $status);
            }
        }

        return true;
    }

    public static function notificationCallbackPaymnet()
    {

        \Midtrans\Config::$isProduction = env("MIDTRANS_IS_PROD");
        \Midtrans\Config::$serverKey = env("MIDTRANS_SERVER_KEY");

        $notif = new \Midtrans\Notification();

        $transaction = $notif->transaction_status;
        $type = $notif->payment_type;
        $order_id = $notif->order_id;
        $fraud = $notif->fraud_status;

        $status = 3;

        $transaction_log = TransactionLog::where("order_id", $order_id)->first();
        if (!$transaction_log || $transaction_log->status == 1) {
            return false;
        }
        if ($transaction == 'settlement') {
            $status = 1;
        } else if ($transaction == 'pending') {
            $status = 4;
            $transaction_log->expired_time = strtotime("+" . env("MIDTRANS_EXPIRE_DURATION_SNAP_TOKEN_ON_MINUTE", 30) . " minutes", time());
        } else if ($transaction == 'expire') {
            $status = 2;
        }

        $transaction_log->status = $status;
        $activity = \json_decode($transaction_log->transaction_log_activity, true);
        $activity["notification_callback_" . $status] = \json_encode($notif->getResponse());
        $transaction_log->transaction_log_activity = \json_encode($activity);
        $transaction_log->save();

        if (substr($transaction_log->order_id, 0, strlen(env("ORDER_OFFICIAL_ID_PREFIX"))) == env("ORDER_OFFICIAL_ID_PREFIX")) {
            if ($status == 1) {
                return self::orderOfficial($transaction_log, $status);
            }
        } elseif (substr($transaction_log->order_id, 0, strlen(env("ORDER_ID_PREFIX"))) == env("ORDER_ID_PREFIX")) {
            ArcheryEventParticipant::where("transaction_log_id", $transaction_log->id)->update(["status" => $status]);
            if ($status == 1) {
                return self::orderEvent($transaction_log, $status);
            }
        }


        return true;
    }

    private static function orderEvent($transaction_log, $status)
    {
        $participant = ArcheryEventParticipant::where('transaction_log_id', $transaction_log->id)->first();
        if (!$participant) {
            throw new BLoCException("participant data not found");
        }

        $event_category_detail = ArcheryEventCategoryDetail::find($participant->event_category_id);
        if (!$event_category_detail) {
            throw new BLoCException("category not found");
        }

        if ($event_category_detail->category_team == 'Team') {
            // $temporary_participant_member = TemporaryParticipantMember::where('participant_id', $participant->id)->where('event_category_id', $event_category_detail->id)->get();
            // foreach ($temporary_participant_member as $tmp) {
            //     ParticipantMemberTeam::create([
            //         'participant_id' => $tmp->participant_id,
            //         'participant_member_id' => $tmp->participant_member_id,
            //         'event_category_id' => $event_category_detail->id,
            //         'type' => $event_category_detail->category_team,
            //     ]);
            // }
        } else {
            $participant_member = ArcheryEventParticipantMember::where('archery_event_participant_id', $participant->id)->first();
            $qualification_time = ArcheryEventQualificationTime::where('category_detail_id', $event_category_detail->id)->first();

            $user = User::find($participant_member->user_id);
            if (!$user) {
                throw new BLoCException("user not found");
            }
            ArcheryEventParticipantNumber::saveNumber(ArcheryEventParticipantNumber::makePrefix($event_category_detail->id, $user->gender), $participant->id);
            ArcheryEventParticipantMemberNumber::saveMemberNumber(ArcheryEventParticipantMemberNumber::makePrefix($event_category_detail->event_id, $user->gender), $participant_member->user_id, $event_category_detail->event_id);
            $key = env("REDIS_KEY_PREFIX") . ":qualification:score-sheet:updated";
            Redis::hset($key, $event_category_detail->id, $event_category_detail->id);
            ArcheryEventQualificationScheduleFullDay::create([
                'qalification_time_id' => $qualification_time->id,
                'participant_member_id' => $participant_member->id,
            ]);

            ParticipantMemberTeam::saveParticipantMemberTeam($event_category_detail->id, $participant->id, $participant_member->id, "individual");
            ArcherySeriesUserPoint::setAutoUserMemberCategory($event_category_detail->event_id,$user->id);
        }

        // create cash flow
        if($status == 1){
            $event = ArcheryEvent::where('id',$participant->event_id)->first();
            $have_payment_fee = $event->include_payment_gateway_fee_to_user ? true : false;
            $admin_have_event = Admin::where('id',$event->admin_id)->first();
            $category_label = ArcheryEventCategoryDetail::getCategoryLabelComplete($event_category_detail->id);
            $note = $event->name." (".$category_label.")";
            $cash_flow[] = [
                    'eo_id' => $admin_have_event->eo_id,
                    'note' => "[register event] ".$note,
                    'gateway' => $transaction_log->gateway,
                    'transaction_log_id' => $transaction_log->id,
                    'amount' => $transaction_log->total_amount,
            ];
            if(!$have_payment_fee && $transaction_log->gateway == "OY"){
                $gateway_fee =self::getPaymentFee(self::$oy_callback->payment_method,self::$oy_callback->sender_bank,$transaction_log->amount,true);
                if( $gateway_fee > 0){
                $cash_flow[] = [
                    'eo_id' => $admin_have_event->eo_id,
                    'note' => "[fee payment register event] ".$note,
                    'gateway' => $transaction_log->gateway,
                    'transaction_log_id' => $transaction_log->id,
                    'amount' => -1 * $gateway_fee,
                ];
                }
            }
            if($transaction_log->include_my_archery_fee > 0){
                $cash_flow[] = [
                    'eo_id' => $admin_have_event->eo_id,
                    'note' => "[fee MyArchery register event] ".$note,
                    'gateway' => $transaction_log->gateway,
                    'transaction_log_id' => $transaction_log->id,
                    'amount' => -1 * $transaction_log->include_my_archery_fee,
                ];
            }
            
            EoCashFlow::insert($cash_flow);
        }
    }

    private static function orderOfficial($transaction_log, $status)
    {
        $archery_event_official = ArcheryEventOfficial::where('transaction_log_id', $transaction_log->id)->first();
        if (!$archery_event_official) {
            throw new BLoCException("perubahan status official gagal");
        }

        $archery_event_official->update([
            'status' => $status
        ]);

        // create cash flow
        if($status == 1){
            $event_official_detail = ArcheryEventOfficialDetail::where('id',$archery_event_official->event_official_detail_id)->first();
            $event = ArcheryEvent::where('id',$event_official_detail->event_id)->first();
            $admin_have_event = Admin::where('id',$event->admin_id)->first();
            $cash_flow[] = [
                    'eo_id' => $admin_have_event->eo_id,
                    'note' => "[register official event] ".$event->name,
                    'gateway' => $transaction_log->gateway,
                    'transaction_log_id' => $transaction_log->id,
                    'amount' => $transaction_log->total_amount,
            ];
             if(!$have_payment_fee && $transaction_log->gateway == "OY"){
                $gateway_fee =self::getPaymentFee(self::$oy_callback->payment_method,self::$oy_callback->sender_bank,$transaction_log->amount,true);
                if( $gateway_fee > 0){
                    $cash_flow[] = [
                        'eo_id' => $admin_have_event->eo_id,
                        'note' => "[fee MyArchery register official event] ".$event->name,
                        'gateway' => $transaction_log->gateway,
                        'transaction_log_id' => $transaction_log->id,
                        'amount' => -1 * $gateway_fee
                    ];
                }
            }
            if($transaction_log->include_my_archery_fee > 0){
                $cash_flow[] = [
                    'eo_id' => $admin_have_event->eo_id,
                    'note' => "[fee MyArchery register official event] ".$event->name,
                    'gateway' => $transaction_log->gateway,
                    'transaction_log_id' => $transaction_log->id,
                    'amount' => $transaction_log->total_amount,
                ];
            }
            EoCashFlow::insert($cash_flow);
        }
    }
}
