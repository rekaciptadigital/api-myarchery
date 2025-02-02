<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantNumber;
use App\Models\ArcheryEventParticipantMember;
use DAI\Utils\Abstracts\Transactional;
use App\Libraries\PaymentGateWay;
use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use App\Models\ArcheryEventEmailWhiteList;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventQualificationScheduleFullDay;
use App\Models\ArcheryEventQualificationTime;
use App\Models\ParticipantMemberTeam;
use App\Models\ArcheryEventParticipantMemberNumber;
use App\Models\ArcheryMasterAgeCategory;
use App\Models\ArcherySeriesUserPoint;
use App\Models\ChildrenClassificationMembers;
use App\Models\City;
use App\Models\CityCountry;
use App\Models\Country;
use App\Models\OrderEvent;
use App\Models\ProvinceCountry;
use App\Models\Provinces;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;

class AddEventOrderV2 extends Transactional
{
    var $gateway = "";
    var $have_fee_payment_gateway = false;
    var $payment_methode = "";
    var $myarchery_fee = 0;
    public function getDescription()
    {
        return "";
        /*
            # order individu
                db insert :
                            - archery_event_participants
                            - archery_event_participant_members
                            - transaction_log (for have fee)
                            - archery_event_participant_member_numbers (if free)
                            - archery_event_qualification_schedule_full_days (if free)
                            - participant_member_team (if free)
        */
    }

    protected function process($parameters)
    {
        $this->payment_methode = $parameters->get('payment_methode') ? $parameters->get('payment_methode    ') : "bankTransfer";

        $user_login = Auth::guard('app-api')->user();
        // $club_or_city_id = $parameters->get("club_or_city_id");
        $this->gateway = $parameters->get("gateway") ? $parameters->get("gateway") : env("PAYMENT_GATEWAY", "midtrans");
        $members = $parameters->get("members");
        $event_id = $parameters->get("event_id");
        $get_city_id = $parameters->get("classification_city");
        $get_province_id = $parameters->get("classification_province");
        $get_country_id = $parameters->get("classification_country");
        $get_club_id = $parameters->get("classification_club_id");
        $get_children_id = $parameters->get("classification_children_id");

        $event = ArcheryEvent::find($event_id);

        $city_id = 0;
        $club_id = 0;
        $classification_country_id = 0;
        $classification_province_id = 0;
        $classification_children_id = 0;


        if ($event['parent_classification'] == 1) {
            if (!empty($get_club_id)) {
                $club = ArcheryClub::find($get_club_id);

                if (empty($club)) {
                    throw new BLoCException("club not found!");
                }

                $club_id = $get_club_id;
            } else {
                $club_id = 0;
            }
        } elseif ($event['parent_classification'] == 2) {
            if (empty($get_country_id)) {
                throw new BLoCException("country is required, because contingent territory of the country!");
            }
            $country = Country::find($get_country_id);
            if (empty($country)) {
                throw new BLoCException("country not found!");
            }

            $classification_country_id = $get_country_id;
        } elseif ($event['parent_classification'] == 3) {
            $query_province = false;
            if (empty($get_province_id)) {
                throw new BLoCException("province is required, because contingent territory of the province!");
            }

            if ($event['classification_country_id'] == 102) {
                $query_province = Provinces::find($get_province_id);
            } else {
                $query_province = ProvinceCountry::where('country_id', '=', $event['classification_country_id'])
                    ->where('id', '=', $get_province_id)
                    ->first();
            }

            if (empty($query_province)) {
                throw new BLoCException("province not found!");
            }

            $classification_country_id = $event['classification_country_id'];
            $classification_province_id = $get_province_id;
        } elseif ($event['parent_classification'] == 4) {
            $query_city = false;
            if (empty($get_city_id)) {
                throw new BLoCException("city is required, because contingent territory of the city!");
            }

            if ($event['classification_country_id'] == 102) {
                $query_city = City::find($get_city_id);
            } else {
                $query_city = CityCountry::where('state_id', '=', $event['province_id'])
                    ->where('country_id', '=', $event['classification_country_id'])
                    ->where('id', '=', $get_city_id)
                    ->first();
            }

            if (empty($query_city)) {
                throw new BLoCException("city not found!");
            }

            $classification_country_id = $event['classification_country_id'];
            $classification_province_id = $event['province_id'];
            $city_id = $get_city_id;
        } else {
            if (empty($get_children_id)) {
                throw new BLoCException("children classification is required!");
            }

            $check_child = ChildrenClassificationMembers::where('id', '=', $get_children_id)->where('parent_id', '=', $event['parent_classification'])->first();

            if (empty($check_child)) {
                throw new BLoCException("children classification not found!");
            }

            $classification_children_id = $get_children_id;
        }

        $order_event = OrderEvent::saveOrderEvent($user_login->id, 4, 0, 0, 0, $event->id);

        $total_price = 0;
        $with_early_bird = 0;
        foreach ($members as $key => $m) {
            $event_category_detail = ArcheryEventCategoryDetail::where("id", $m["event_category_id"])->where("event_id", $event->id)->first();
            if (!$event_category_detail) {
                throw new BLoCException("category not found");
            }
            // dapatkan harga category
            $price_with_early_bird = ArcheryEventCategoryDetail::getPriceCategory($event_category_detail);
            $total_price += $price_with_early_bird->price;
            $with_early_bird = $price_with_early_bird->with_early_bird;

            $user_new = User::where("email", $m["email"])->first();
            if (!$user_new) {
                $user_new = new User;
                $user_new->gender = $m["gender"];
                $user_new->name = $m["name"];
                $user_new->password = Hash::make("12345678");
                $user_new->email = $m["email"];
                $user_new->date_of_birth = date("Y-m-d", strtotime($m["date_of_birth"]));
            }

            if ($m["country_id"] == 102) {
                $user_new->is_wna = 0;
                $province = Provinces::find($m["province_id"]);
                if (!$province) {
                    throw new BLoCException("province not found");
                }

                $user_new->address_province_id = $province->id;

                $city = City::where("id", $m["city_id"])
                    ->where("province_id", $m["province_id"])
                    ->first();
                if (!$city) {
                    throw new BLoCException("city not found");
                }

                $user_new->address_city_id  = $city->id;
            } else {
                $user_new->is_wna = 1;
                $country = Country::find($m["country_id"]);
                if (!$country) {
                    throw new BLoCException("country not found");
                }

                $user_new->country_id = $country->id;

                $province = ProvinceCountry::where("country_id", $m["country_id"])
                    ->where("id", $m["province_id"])
                    ->first();

                if (!$province) {
                    throw new BLoCException("province not found");
                }

                if (isset($m["city_id"])) {
                    $city = CityCountry::where("id", $m["city_id"])
                        ->where("state_id", $m["province_id"])
                        ->where("country_id", $m["country_id"])
                        ->first();

                    if ($city) {
                        $user_new->city_of_country_id = $city->id;
                    }
                }
            }
            $user_new->save();

            if ($event->is_private) {
                $check_email_whitelist = ArcheryEventEmailWhiteList::where("email", $user_new->email)
                    ->where("event_id", $event->id)
                    ->first();
                if (!$check_email_whitelist) {
                    throw new BLoCException("Mohon maaf akun anda tidak terdaftar sebagai peserta");
                }
            }

            // blok: cek waktu pendaftaran
            $check_datetime_can_order_event = ArcheryEvent::checkIsCanOrderEventByDatetimeOrder($event, $event_category_detail);
            if ($check_datetime_can_order_event != 1) {
                throw new BLoCException($check_datetime_can_order_event);
            }
            // end blok : cek waktu pendaftaran

            $time_now = time();


            $qualification_time = ArcheryEventQualificationTime::where('category_detail_id', $event_category_detail->id)->first();
            if (!$qualification_time) {
                throw new BLoCException('event belum bisa di daftar');
            }

            // hitung jumlah participant pada category yang didaftarkan user
            $participant_count = ArcheryEventParticipant::countEventUserBooking($event_category_detail->id);
            if ($participant_count > $event_category_detail->quota) {
                $msg = "quota kategori ini sudah penuh";
                // check kalo ada pembayaran yang pending
                $participant_count_pending = ArcheryEventParticipant::join("transaction_logs", "transaction_logs.id", "=", "archery_event_participants.transaction_log_id")
                    ->where("event_category_id", $event_category_detail->id)
                    ->where("archery_event_participants.status", 4)
                    ->where("transaction_logs.status", 4)
                    ->where("transaction_logs.expired_time", ">", $time_now)
                    ->where("event_id", $event_category_detail->event_id)->count();

                if ($participant_count_pending > 0) {
                    $msg = "untuk sementara  " . $msg . ", silahkan coba beberapa saat lagi";
                } else {
                    $msg = $msg . ", silahkan daftar di kategori lain";
                }
                throw new BLoCException($msg);
            }

            $age_categoy = ArcheryMasterAgeCategory::find($event_category_detail->age_category_id);
            if (!$age_categoy) {
                throw new BLoCException("age category not found");
            }

            $check_age = ArcheryEvent::checUserAgeCanOrderCategory($user_new->date_of_birth, $age_categoy, $event);
            if ($check_age != 1) {
                throw new BLoCException($check_age);
            }

            $gender_category = $event_category_detail->gender_category;
            if ($event->event_type == "Full_day") {
                if ($user_new->gender != $gender_category) {
                    if ($gender_category != "mix") {
                        throw new BLoCException('oops.. kategori ini  hanya untuk gender ' . $gender_category);
                    }

                    if (!$user_new->gender) {
                        throw new BLoCException("gender empty");
                    }
                }
            }

            $check_member_success =  ArcheryEventParticipantMember::join(
                "archery_event_participants",
                "archery_event_participants.id",
                "=",
                "archery_event_participant_members.archery_event_participant_id"
            )->where("archery_event_participant_members.user_id", $user_new->id)
                ->where("archery_event_participants.status", 1)
                ->where("archery_event_participants.event_category_id", $event_category_detail->id)
                ->first();

            if ($check_member_success) {
                throw new BLoCException("user telah mengikuti kategori ini");
            }

            $check_member_pending =  ArcheryEventParticipantMember::join(
                "archery_event_participants",
                "archery_event_participants.id",
                "=",
                "archery_event_participant_members.archery_event_participant_id"
            )->join(
                "transaction_logs",
                "transaction_logs.id",
                "=",
                "archery_event_participants.transaction_log_id"
            )
                ->where("archery_event_participant_members.user_id", $user_new->id)
                ->where("archery_event_participants.status", 4)
                ->where("archery_event_participants.event_category_id", $event_category_detail->id)
                ->where("transaction_logs.status", 4)
                ->where("transaction_logs.expired_time", ">", $time_now)
                ->first();

            if ($check_member_pending) {
                throw new BLoCException("user telah mendaftar kategori ini, dan transaksi sedang berlangsung");
            }

            // $participant = ArcheryEventParticipant::saveArcheryEventParticipant($user_new, $event_category_detail, "individual", 0, Str::uuid(), null, null, 4, $club_id, null, null, 1, 1, null, 0, $with_early_bird, 0, $city_id, $order_event->id);

            $participant = ArcheryEventParticipant::saveArcheryEventParticipant($user_new, $event_category_detail, "individual", 0, Str::uuid(), null, null, 4, $club_id, null, null, 1, 1, null, 0, $with_early_bird, 0, $city_id, $order_event->id, $classification_country_id, $classification_province_id, $classification_children_id);
            $member = ArcheryEventParticipantMember::saveArcheryEventParticipantMember($participant, $user_new, $event_category_detail, 0);
        }

        $order_id = env("ORDER_ID_PREFIX", "OE-S") . "-" . Str::uuid() . "-" . $order_event->id;

        if ($total_price < 1) {

            $order_event->status = 1;
            $order_event->total_price = 0;
            $order_event->save();

            $list_participants = ArcheryEventParticipant::where("order_event_id", $order_event->id)->get();

            foreach ($list_participants as $key_lp => $lp) {
                $lp->status = $order_event->status;
                $lp->save();

                $member_participant = ArcheryEventParticipantMember::where("archery_event_participant_id", $lp->id)->first();
                if (!$member_participant) {
                    throw new BLoCException("member not found");
                }

                ArcheryEventParticipantNumber::saveNumber(ArcheryEventParticipantNumber::makePrefix($lp->event_category_id, $member_participant->gender), $lp->id);
                ArcheryEventParticipantMemberNumber::saveMemberNumber(ArcheryEventParticipantMemberNumber::makePrefix($event->id, $member_participant->gender), $member_participant->user_id, $event->id);
                $key = env("REDIS_KEY_PREFIX") . ":qualification:score-sheet:updated";
                $qualification_time = ArcheryEventQualificationTime::where('category_detail_id', $lp->event_category_id)->first();
                Redis::hset($key, $lp->event_category_id, $lp->event_category_id);
                ArcheryEventQualificationScheduleFullDay::create([
                    'qalification_time_id' => $qualification_time->id,
                    'participant_member_id' => $member_participant->id,
                ]);
                ParticipantMemberTeam::saveParticipantMemberTeam($lp->event_category_id, $lp->id, $member_participant->id, $lp->type);

                $res = [
                    "archery_event_participant_id" => $participant->id,
                    "payment_info" => null
                ];
                ArcherySeriesUserPoint::setAutoUserMemberCategory($event->id, $member_participant->user_id);
            }

            $res = [
                "order_event_id" => $order_event->id,
                'payment_info' => null
            ];

            return $res;
        }

        if ($event->my_archery_fee_percentage > 0) {
            $this->myarchery_fee = round($total_price * ($event->my_archery_fee_percentage / 100));
        }

        $this->have_fee_payment_gateway = $event->include_payment_gateway_fee_to_user > 0 ? true : false;

        $payment = PaymentGateWay::setTransactionDetail((int)$total_price, $order_id)
            ->setGateway($this->gateway)
            ->setCustomerDetails($user_login->name, $user_login->email, $user_login->phone_number)
            ->addItemDetail($event->id, (int)$total_price, $event->event_name)
            ->feePaymentsToUser($this->have_fee_payment_gateway)
            ->setMyarcheryFee($this->myarchery_fee)
            ->createSnap($event->event_name);
        if (!$payment->status) {
            throw new BLoCException($payment->message);
        }

        $order_event->transaction_log_id = $payment->transaction_log_id;
        $order_event->total_price = (int)$total_price;
        $order_event->save();

        $list_participants = ArcheryEventParticipant::where("order_event_id", $order_event->id)->get();
        foreach ($list_participants as $key => $lp) {
            $lp->transaction_log_id = $order_event->transaction_log_id;
            $lp->save();
        }

        $participant = ArcheryEventParticipant::where('user_id', '=', $user_login->id)
            ->where('event_id', '=', $event_id)
            ->orderBy('id', 'desc')
            ->first();

        $res = [
            "order_event_id" => $order_event->id,
            'payment_info' => $payment,
            'participant' => $participant
        ];

        return $res;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|exists:archery_events,id",
            "members" => "required|array",
            "members.*.event_category_id" => "required|exists:archery_event_category_details,id",
            "members.*.email" => "required|email",
            "members.*.gender" => "required|in:male,female",
            "members.*.date_of_birth" => "required",
            "members.*.name" => "required",
            "members.*.country_id" => "required",
            "members.*.province_id" => "required",
        ];
    }
}
