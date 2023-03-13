<?php

namespace App\BLoC\Web\EventOrder;

use App\Models\ArcheryEventParticipant;
use DAI\Utils\Abstracts\Transactional;
use App\Libraries\PaymentGateWay;
use App\Models\ArcheryClub;
use App\Models\ArcheryEvent;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventEmailWhiteList;
use App\Models\ChildrenClassificationMembers;
use App\Models\City;
use App\Models\CityCountry;
use App\Models\Country;
use App\Models\OrderEvent;
use App\Models\ProvinceCountry;
use App\Models\Provinces;

class AddEventOrderTeam extends Transactional
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
        $this->payment_methode = $parameters->get('payment_methode') ? $parameters->get('payment_methode') : "bankTransfer";

        $user_login = Auth::guard('app-api')->user();
        // $club_or_city_id = $parameters->get("club_or_city_id");
        $get_country_id = $parameters->get("classification_country_id");
        $get_province_id = $parameters->get("classification_province_id");
        $get_city_id = $parameters->get("classification_city_id");
        $get_club_id = $parameters->get("classification_club_id");
        $get_children_id = $parameters->get("classification_children_id");

        $this->gateway = $parameters->get("gateway") ? $parameters->get("gateway") : env("PAYMENT_GATEWAY", "midtrans");
        $event_id = $parameters->get("event_id");
        $event_category_id = $parameters->get("event_category_id");
        $total_slot = $parameters->get("total_slot");

        $event = ArcheryEvent::find($event_id);

        $category = ArcheryEventCategoryDetail::select(
            "archery_event_category_details.*",
            "archery_master_team_categories.type as type_category"
        )->join(
            "archery_master_team_categories",
            "archery_master_team_categories.id",
            "=",
            "archery_event_category_details.team_category_id"
        )->where("archery_event_category_details.id", $event_category_id)
            ->where("archery_master_team_categories.type", "Team")
            ->where("archery_event_category_details.event_id", $event_id)
            ->first();


        if (!$category) {
            throw new BLoCException("category not found");
        }

        if ($event->is_private) {
            $check_email_whitelist = ArcheryEventEmailWhiteList::where("email", $user_login->email)
                ->where("event_id", $event->id)
                ->first();
            if (!$check_email_whitelist) {
                throw new BLoCException("Mohon maaf akun anda tidak terdaftar sebagai peserta");
            }
        }

        // blok: cek waktu pendaftaran
        $check_datetime_can_order_event = ArcheryEvent::checkIsCanOrderEventByDatetimeOrder($event, $category);
        if ($check_datetime_can_order_event != 1) {
            throw new BLoCException($check_datetime_can_order_event);
        }
        // end blok : cek waktu pendaftaran

        $city_id = 0;
        $club_id = 0;
        $classification_country_id = 0;
        $classification_province_id = 0;
        $classification_children_id = 0;

        if ($event->with_contingent == 1) {
            if ($event['parent_classification'] == 1) {
                if (empty($get_club_id)) {
                    throw new BLoCException("club is required, because contingent club!");
                }

                $club = ArcheryClub::find($get_club_id);
                if (empty($club)) {
                    throw new BLoCException("club not found!");
                }

                $club_id = $get_club_id;
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
        }

        $classification_contingent = [
            "city_id" => $city_id,
            "club_id" => $club_id,
            "country_id" => $classification_country_id,
            "province_id" => $classification_province_id,
            "children_id" => $classification_children_id,
        ];
        // if ($event->with_contingent == 1) {
        //     $city = City::where("id", $club_or_city_id)
        //         ->where("province_id", $event->province_id)
        //         ->first();
        //     if (!$city) {
        //         throw new BLoCException("city not found");
        //     }

        //     $city_id = $city->id;
        // } else {
        //     $club = ArcheryClub::find($club_or_city_id);
        //     if (!$club) {
        //         throw new BLoCException("club not found");
        //     }
        //     $club_id = $club->id;
        // }


        // $count_participant_team = ArcheryEventParticipant::getCountParticipantTeamWithSameClubOrCity($category, $event, $club_or_city_id);
        $count_participant_team = ArcheryEventParticipant::getCountParticipantTeamWithSameWithContingent($category, $event, $classification_contingent);

        $total_participant_team = $total_slot + $count_participant_team;


        // validasi total peserta individu untuk pendaftaran beregu
        if ($category->team_category_id == "male_team" || $category->team_category_id == "female_team") {

            $team_category_id = $category->team_category_id == "male_team" ? "individu male" : "individu female";

            // $count_participant_individu = ArcheryEventParticipant::getCountParticipantIndividuByCategoryTeam($category, $event, $club_or_city_id, $team_category_id);

            $count_participant_individu = ArcheryEventParticipant::getCountParticipantIndividuByCategoryTeamContingent($category, $event, $classification_contingent, $team_category_id);
            $data = [];
            if ($count_participant_individu == 0) {
                throw new BLoCException("participant individu not found");
            }

            $tmp = $count_participant_individu / 3;

            if ($tmp < $total_participant_team) {
                $total_member_individu_must_join = $total_participant_team * 3;
                throw new BLoCException("jumlah peserta tidak mencukupi, minimal peserta yang harus terdaftar adalah " . $total_member_individu_must_join . ". sedangkan total peserta individu saat ini adalah " . $count_participant_individu . " peserta");
            }
        } else {
            // $count_participant_individu_male = ArcheryEventParticipant::getCountParticipantIndividuByCategoryTeam($category, $event, $club_or_city_id, "individu male");
            // $count_participant_individu_female = ArcheryEventParticipant::getCountParticipantIndividuByCategoryTeam($category, $event, $club_or_city_id, "individu female");

            $count_participant_individu_male = ArcheryEventParticipant::getCountParticipantIndividuByCategoryTeamContingent($category, $event, $classification_contingent, "individu male");
            $count_participant_individu_female = ArcheryEventParticipant::getCountParticipantIndividuByCategoryTeamContingent($category, $event, $classification_contingent, "individu female");

            if ($count_participant_individu_male == 0 || $count_participant_individu_female == 0) {
                throw new BLoCException("participant not enought");
            }

            if ($count_participant_individu_male < $total_participant_team) {
                throw new BLoCException("jumlah peserta tidak mencukupi, minimal peserta male yang harus terdaftar adalah " . $total_participant_team . ". sedangkan total peserta individu male saat ini adalah " . $count_participant_individu_male . " peserta");
            }

            if ($count_participant_individu_female < $total_participant_team) {
                throw new BLoCException("jumlah peserta tidak mencukupi, minimal peserta female yang harus terdaftar adalah " . $total_participant_team . ". sedangkan total peserta individu female saat ini adalah " . $count_participant_individu_female . " peserta");
            }
        }
        $order_event = OrderEvent::saveOrderEvent($user_login->id, 4, 0, 0, 0);

        $price_with_early_bird = ArcheryEventCategoryDetail::getPriceCategory($category);
        $total_price = (int)$price_with_early_bird->price * $total_slot;
        $with_early_bird = $price_with_early_bird->with_early_bird;

        for ($i = 1; $i <= $total_slot; $i++) {
            $participant_team = ArcheryEventParticipant::saveArcheryEventParticipant($user_login, $category, "team", 0, Str::uuid(), null, null, 4, $club_id, null, null, 1, 1, null, 0, $with_early_bird, 0, $city_id, $order_event->id, $classification_country_id, $classification_province_id, $classification_children_id);
        }

        if ($total_price < 1) {
            $order_event->status = 1;
            $order_event->total_price = 0;
            $order_event->save();

            $list_participants = ArcheryEventParticipant::where("order_event_id", $order_event->id)->get();
            foreach ($list_participants as $key => $p) {
                $p->status = $order_event->status;
                $p->save();
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
        $order_id = env("ORDER_ID_PREFIX", "OE-S") . $order_event->id;

        $payment = PaymentGateWay::setTransactionDetail((int)$total_price, $order_id)
            ->setGateway($this->gateway)
            ->setCustomerDetails($user_login->name, $user_login->email, $user_login->phone_number)
            ->addItemDetail($event->id, (int)$total_price, $event->event_name)
            ->feePaymentsToUser($this->have_fee_payment_gateway)
            ->setMyarcheryFee($this->myarchery_fee)
            ->createSnap();
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

        $res = [
            "order_event_id" => $order_event->id,
            'payment_info' => $payment
        ];

        return $res;
    }

    protected function validation($parameters)
    {
        return [
            "event_id" => "required|exists:archery_events,id",
            // "club_or_city_id" => "required",
            "event_category_id" => "required|exists:archery_event_category_details,id",
            "total_slot" => "required|min:1",
        ];
    }
}
