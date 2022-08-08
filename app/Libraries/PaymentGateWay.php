<?php

namespace App\Libraries;

use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventOfficial;
use App\Models\TransactionLog;

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
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Redis;

class PaymentGateWay
{

    static $transaction_details = array();
    static $customer_details = array();
    static $payment_gateway_fee = 0;
    static $fee_myarchery = 0;
    static $gateway = "midtrans";
    static $enabled_payments = [
        "bank_transfer"
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

    public static function setMyarcheryFee(Double $amount)
    {
        self::$fee_myarchery = $amount;
        return (new self);
    }

    public static function setGateway(string $gateway)
    {
        self::$gateway = $gateway;
        return (new self);
    }

    // sampel payments
    // ["credit_card", "cimb_clicks",
    // "bca_klikbca", "bca_klikpay", "bri_epay", "echannel", "permata_va",
    // "bca_va", "bni_va", "bri_va", "other_va", "gopay", "indomaret",
    // "danamon_online", "akulaku", "shopeepay"]
    public static function enabledPayments(string $payment_methode,bool $have_fee = false)
    {
        
        $list = [
            "midtrans" => [
                "bank_transfer" => [
                    "list" => ["bank_transfer"],
                    "fee_type" => "nominal",
                    "fee" => 4000
                ],
                "gopay" => [
                    "list" => ["gopay"],
                    "fee_type" => "percentage",
                    "fee" => 2
                ],
                ]
            ];
            
        $enabled_payments = [];
        if(isset($list[self::$gateway]) && isset($list[self::$gateway][$payment_methode])){
            $enabled_payments = array_merge($enabled_payments,$list[self::$gateway][$payment_methode]["list"]);
            if($have_fee){
                if($list[self::$gateway][$payment_methode]["fee_type"] == "percentage"){
                    $transaction_details = self::$transaction_details;
                    self::$payment_gateway_fee = round($transaction_details["gross_amount"] * ($list[self::$gateway][$payment_methode]["fee"]/100));
                }else{
                    self::$payment_gateway_fee = $list[self::$gateway][$payment_methode]["fee"];
                }
            }
        }
        self::$enabled_payments = $enabled_payments;
        return (new self);
    }

    public static function getPaymentMethode(bool $have_fee = false)
    {
        
        $list = [
            "midtrans" => [
                "bank_transfer" => [
                    "label" => "Transfer Bank",
                    "list" => [""],
                    "fee_type" => "nominal",
                    "fee" => 4000,
                    "active" => $have_fee
                ],
                "gopay" => [
                    "label" => "Gopay",
                    "list" => ["gopay"],
                    "fee_type" => "percentage",
                    "fee" => 2,
                    "active" => $have_fee
                ],
                ]
            ];
    
        return $list[self::$gateway];
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
        $transaction_details = self::$transaction_details;
        $customer_details = self::$customer_details;
        $transaction_details["gross_amount"] = $transaction_details["gross_amount"] + self::$payment_gateway_fee;
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

        $snap_token = \Midtrans\Snap::getSnapToken($params);
        if ($snap_token) {
            $activity = array("request_snap_token" => array("params" => $params, "response" => $snap_token));
            $transaction_log = new TransactionLog;
            $transaction_log->order_id = $transaction_details["order_id"];
            $transaction_log->transaction_log_activity = json_encode($activity);
            $transaction_log->amount = $transaction_details["gross_amount"] + self::$payment_gateway_fee;
            $transaction_log->status = 4;
            $transaction_log->expired_time = $expired_time;
            $transaction_log->token = $snap_token;
            $transaction_log->include_payment_gateway_fee = self::$payment_gateway_fee;
            $transaction_log->save();
        }
        return (object)[
            "order_id" => $transaction_details["order_id"],
            "total" => $transaction_details["gross_amount"],
            "status" => TransactionLog::getStatus(0),
            "transaction_log_id" => $transaction_log->id,
            "snap_token" => $snap_token,
            "client_key" => env("MIDTRANS_CLIENT_KEY"),
            "client_lib_link" => env("MIDTRANS_CLIENT_LIB_LINK")
        ];
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
            "total" => $transaction_log->amount,
            "status_id" => $status,
            "status" => TransactionLog::getStatus($status),
            "transaction_log_id" => $transaction_log->id,
            "snap_token" => $transaction_log->token,
            "client_key" => env("MIDTRANS_CLIENT_KEY"),
            "client_lib_link" => env("MIDTRANS_CLIENT_LIB_LINK")
        ];
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
                return self::orderEvent($transaction_log);
            }
        }


        return true;
    }

    private static function orderEvent($transaction_log)
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
    }
}
