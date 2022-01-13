<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEvent;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Abstracts\Transactional;
use App\Libraries\PaymentGateWay;
use DAI\Utils\Exceptions\BLoCException;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\ArcheryEventCategory;
use App\Models\ArcheryEventCategoryCompetitionTeam;
use App\Models\ArcheryEventCategoryCompetition;
use App\Models\ArcheryEventCategoryDetail;

class AddEventOrder extends Transactional
{
    public function getDescription()
    {
        return "";
    }

    protected function process($parameters)
    {
        $user = Auth::guard('app-api')->user();
        $total_price = 0;

        $event_category_id = $parameters->get('event_category_id');

        // get event_category_detail by id
        $event_category_detail = ArcheryEventCategoryDetail::find($event_category_id);

        if ($event_category_detail->category_team == ArcheryEventCategoryDetail::INDIVIDUAL_TYPE) {
            return $this->registerIndividu($event_category_detail);
        } else {
            return "ok team";
        }
    }

    private function registerIndividu($parameters)
    {
        $time_now = time();
        $participant_count = ArcheryEventParticipant::where("event_category_id", $parameters->get('event_category_id'))
        ->where('status', 1)->orWhere(function($query) use ($time_now){
            $query->where("status", 4);
            $query->where("transaction_logs.expired_time",">", $time_now);
        })->where('event_id', $parameters->event_id);
           

        return $participant_count;
        // if ($participant_count >= $parameters->quota) {
        //     $msg = "quota kategori ini sudah penuh";
        //     // check kalo ada pembayaran yang pending
        //     $participant_count_pending = ArcheryEventParticipant::join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")->
        //         // where event_category_id
        //         where("competition_category_id", $event_category['competition_category_id'])->where("competition_category_id", $event_category['competition_category_id'])->where("team_category_id", $event_category['team_category_id'])->where("transaction_logs.status", 4)->where("transaction_logs.expired_time", ">", $time_now)->where("event_id", $parameters->event_id)->count();

        //     if ($participant_count_pending > 0) {
        //         $msg = "untuk sementara  " . $msg . ", silahkan coba beberapa saat lagi";
        //     } else {
        //         $msg = $msg . ", silahkan daftar di kategori lain";
        //     }
        //     throw new BLoCException($msg);
        // }

        // get detail archery_master_age_categories by category age id
        // if ($master_age_category_detail->max_age != 0) {
        //     if (is_null($value["birthdate"]) && $value["birthdate"] = '') {
        //         throw new BLoCException("tgl lahir belum di set");
        //     }
        //     $cy = date("Y") - $category->for_age;
        //     $y = explode("-", $value["birthdate"])[0];
        //     if ($y < $cy)
        //         throw new BLoCException("belum memenuhi syarat umur");
        // }

        // $participant = new ArcheryEventParticipant;
        // $participant->event_id = $event->id;
        // $participant->user_id = $user["id"];
        // $participant->name = $parameters->team_name;
        // // FIELD BARU
        // $participant->club_id = $parameters->club_name;


        // $participant->email = $parameters->email;
        // $participant->type = $parameters->type;
        // $participant->phone_number = $parameters->phone;
        // $participant->team_name = $parameters->team_name;
        // $participant->event_category_id = $event_category_id;
        // $participant->transaction_log_id = 0;
        // $participant->status = 4;
        // $participant->unique_id = Str::uuid();
        // $participant->save();

        // $member = array();
        // $order_id = env("ORDER_ID_PREFIX", "OE-S") . $participant->id;

        // $age = null;
        // if (!is_null($value["birthdate"]) && $value["birthdate"] != '') {
        //     $birth_date = explode("-", $value["birthdate"]);
        //     //get age from date or birthdate
        //     $age = (date("md", date("U", mktime(0, 0, 0, $birth_date[2], $birth_date[1], $birth_date[0]))) > date("md")
        //         ? ((date("Y") - $birth_date[0]) - 1)
        //         : (date("Y") - $birth_date[0]));
        // }

        // $member[] = [
        //     "archery_event_participant_id" => $participant->id,
        //     "name" => $value["name"],
        //     "gender" => $value["gender"] != '' ? $value["gender"] : null,
        //     "birthdate" => $value["birthdate"] == '' ? null : $value["birthdate"],
        //     "age" => $age,
        //     "team_category_id" => $event_category['team_category_id']
        // ];
        // ArcheryEventParticipantMember::insert($member);

        // if ($total_price < 1) {
        //     $participant->status = 1;
        //     $participant->save();
        //     $res = ["archery_event_participant_id" => $participant->id];
        //     return $this->composeResponse($res);
        // }

        // $payment = PaymentGateWay::setTransactionDetail($total_price, $order_id)
        //     ->enabledPayments(["bca_va", "bni_va", "bri_va", "other_va", "gopay"])
        //     ->setCustomerDetails($parameters->participant_members[0]["name"], $parameters->email, $parameters->phone)
        //     ->addItemDetail($event->id, $total_price, $event->event_name)
        //     ->createSnap();

        // $participant->transaction_log_id = $payment->transaction_log_id;
        // $participant->save();

        // $res = ["archery_event_participant_id" => $participant->id];
        // return $this->composeResponse($res);
    }

    protected function validation($parameters)
    {
        return [
            "type" => "in:team,individual",
            "category_event" => "required",
            "phone" => "required",
            "event_id" => "required|exists:archery_events,id"
        ];
    }
}
